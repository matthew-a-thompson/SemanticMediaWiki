<?php

namespace SMW\Tests\Integration\JSONScript;

use SMW\ApplicationFactory;
use SMW\DataValueFactory;
use SMW\EventHandler;
use SMW\PropertySpecificationLookup;
use SMW\Tests\JsonTestCaseScriptRunner;
use SMW\Tests\JsonTestCaseFileHandler;
use SMW\Tests\Utils\UtilityFactory;
use SMW\SPARQLStore\TurtleTriplesBuilder;

/**
 * @group semantic-mediawiki
 * @group medium
 *
 * @license GNU GPL v2+
 * @since 2.3
 *
 * @author mwjames
 */
class JsonTestCaseScriptRunnerTest extends JsonTestCaseScriptRunner {

	/**
	 * @var QueryTestCaseProcessor
	 */
	private $queryTestCaseProcessor;

	/**
	 * @var RdfTestCaseProcessor
	 */
	private $rdfTestCaseProcessor;

	/**
	 * @var ParserTestCaseProcessor
	 */
	private $parserTestCaseProcessor;

	/**
	 * @var SpecialPageTestCaseProcessor
	 */
	private $specialPageTestCaseProcessor;

	/**
	 * @var RunnerFactory
	 */
	private $runnerFactory;

	/**
	 * @var EventDispatcher
	 */
	private $eventDispatcher;

	/**
	 * @see JsonTestCaseScriptRunner::$deletePagesOnTearDown
	 */
	protected $deletePagesOnTearDown = true;

	protected function setUp() {
		parent::setUp();

		$this->runnerFactory = $this->testEnvironment->getUtilityFactory()->newRunnerFactory();

		$validatorFactory = $this->testEnvironment->getUtilityFactory()->newValidatorFactory();
		$stringValidator = $validatorFactory->newStringValidator();

		$this->queryTestCaseProcessor = new QueryTestCaseProcessor(
			$this->getStore(),
			$validatorFactory->newQueryResultValidator(),
			$stringValidator,
			$validatorFactory->newNumberValidator()
		);

		$this->rdfTestCaseProcessor = new RdfTestCaseProcessor(
			$this->getStore(),
			$stringValidator,
			$this->runnerFactory
		);

		$this->parserTestCaseProcessor = new ParserTestCaseProcessor(
			$this->getStore(),
			$validatorFactory->newSemanticDataValidator(),
			$validatorFactory->newIncomingSemanticDataValidator( $this->getStore() ),
			$stringValidator
		);

		$this->specialPageTestCaseProcessor = new SpecialPageTestCaseProcessor(
			$this->getStore(),
			$stringValidator
		);

		$this->eventDispatcher = EventHandler::getInstance()->getEventDispatcher();

		// This ensures that if content is created in the NS_MEDIAWIKI namespace
		// and an object relies on the MediaWikiNsContentReader then it uses the DB
		ApplicationFactory::getInstance()->getMediaWikiNsContentReader()->skipMessageCache();
		DataValueFactory::getInstance()->clear();

		// Reset the Title/TitleParser otherwise a singleton instance holds an outdated
		// content language reference
		$this->testEnvironment->resetMediaWikiService( '_MediaWikiTitleCodec' );
		$this->testEnvironment->resetMediaWikiService( 'TitleParser' );

		$this->testEnvironment->resetPoolCacheById( PropertySpecificationLookup::POOLCACHE_ID );
		$this->testEnvironment->resetPoolCacheById( TurtleTriplesBuilder::POOLCACHE_ID );

		// Make sure LocalSettings don't interfere with the default settings
		$GLOBALS['smwgDVFeatures'] = $GLOBALS['smwgDVFeatures'] & ~SMW_DV_NUMV_USPACE;
		$GLOBALS['smwgFieldTypeFeatures'] = SMW_FIELDT_NONE;

		$this->testEnvironment->addConfiguration( 'smwgQueryResultCacheType', false );
		$this->testEnvironment->addConfiguration( 'smwgQFilterDuplicates', false );
		$this->testEnvironment->addConfiguration( 'smwgExportResourcesAsIri', false );
		$this->testEnvironment->addConfiguration( 'smwgSparqlReplicationPropertyExemptionList', array() );
	}

	/**
	 * @see JsonTestCaseScriptRunner::getTestCaseLocation
	 */
	protected function getTestCaseLocation() {
		return __DIR__ . '/TestCases';
	}

	/**
	 * @see JsonTestCaseScriptRunner::getTestCaseLocation
	 */
	protected function getRequiredJsonTestCaseMinVersion() {
		return '2';
	}

	/**
	 * @see JsonTestCaseScriptRunner::getAllowedTestCaseFiles
	 */
	protected function getAllowedTestCaseFiles() {
		return array();
	}

	/**
	 * @see JsonTestCaseScriptRunner::runTestCaseFile
	 *
	 * @param JsonTestCaseFileHandler $jsonTestCaseFileHandler
	 */
	protected function runTestCaseFile( JsonTestCaseFileHandler $jsonTestCaseFileHandler ) {

		$this->checkEnvironmentToSkipCurrentTest( $jsonTestCaseFileHandler );

		// Setup
		$this->prepareTest( $jsonTestCaseFileHandler );

		// Before test execution
		$this->doRunBeforeTest( $jsonTestCaseFileHandler );

		// Run test cases
		$this->doRunParserTests( $jsonTestCaseFileHandler );
		$this->doRunSpecialTests( $jsonTestCaseFileHandler );
		$this->doRunRdfTests( $jsonTestCaseFileHandler );
		$this->doRunQueryTests( $jsonTestCaseFileHandler );
	}

	private function prepareTest( $jsonTestCaseFileHandler ) {

		$permittedSettings = array(
			'smwgNamespacesWithSemanticLinks',
			'smwgPageSpecialProperties',
			'smwgNamespace',
			'smwgExportBCNonCanonicalFormUse',
			'smwgExportBCAuxiliaryUse',
			'smwgExportResourcesAsIri',
			'smwgQMaxSize',
			'smwStrictComparators',
			'smwgQSubpropertyDepth',
			'smwgQSubcategoryDepth',
			'smwgQConceptCaching',
			'smwgEnabledInTextAnnotationParserStrictMode',
			'smwgMaxNonExpNumber',
			'smwgDVFeatures',
			'smwgEnabledQueryDependencyLinksStore',
			'smwgEnabledFulltextSearch',
			'smwgFulltextDeferredUpdate',
			'smwgFulltextSearchIndexableDataTypes',
			'smwgFixedProperties',
			'smwgPropertyZeroCountDisplay',
			'smwgQueryResultCacheType',
			'smwgLinksInValues',
			'smwgQFilterDuplicates',
			'smwgQueryProfiler',
			'smwgEntityCollation',
			'smwgSparqlQFeatures',
			'smwgUseCategoryRedirect',
			'smwgQExpensiveThreshold',
			'smwgQExpensiveExecutionLimit',
			'smwgFieldTypeFeatures',

			// MW related
			'wgLanguageCode',
			'wgContLang',
			'wgLang',
			'wgCapitalLinks',
			'wgAllowDisplayTitle',
			'wgRestrictDisplayTitle',
			'wgSearchType',
			'wgEnableUploads',
			'wgFileExtensions',
			'wgDefaultUserOptions'
		);

		foreach ( $permittedSettings as $key ) {
			$this->changeGlobalSettingTo(
				$key,
				$jsonTestCaseFileHandler->getSettingsFor( $key )
			);
		}

		$pageList = $jsonTestCaseFileHandler->getPageCreationSetupList();

		// On some occasions ( e.g. fixed properties) to setup the correct
		// table schema, run the creation once before the content is created
		if ( $jsonTestCaseFileHandler->hasSetting( 'smwgFixedProperties' ) || $jsonTestCaseFileHandler->hasSetting( 'smwgFieldTypeFeatures' ) ) {
			foreach ( $pageList as $page ) {
				if ( isset( $page['namespace'] ) && $page['namespace'] === 'SMW_NS_PROPERTY' ) {
					$this->doRunTableSetupBeforeContentCreation( $page );
				}
			}
		}

		$this->createPagesFrom(
			$pageList,
			NS_MAIN
		);
	}

	private function doRunTableSetupBeforeContentCreation( $page ) {

		// Create property to ...
		$this->createPagesFrom( array( $page ) );

		$maintenanceRunner = $this->runnerFactory->newMaintenanceRunner( 'setupStore' );
		$maintenanceRunner->setQuiet();
		$maintenanceRunner->run();
	}

	private function doRunBeforeTest( $jsonTestCaseFileHandler ) {

		foreach ( $jsonTestCaseFileHandler->findTaskBeforeTestExecutionByType( 'maintenance-run' ) as $runner => $options ) {

			$maintenanceRunner = $this->runnerFactory->newMaintenanceRunner( $runner );
			$maintenanceRunner->setQuiet();

			$maintenanceRunner->setOptions(
				(array)$options
			);

			$maintenanceRunner->run();

			if ( isset( $options['quiet'] ) && $options['quiet'] === false ) {
				print_r( $maintenanceRunner->getOutput() );
			}
		}

		foreach ( $jsonTestCaseFileHandler->findTaskBeforeTestExecutionByType( 'job-run' ) as $jobType ) {
			$jobQueueRunner = $this->runnerFactory->newJobQueueRunner( $jobType );
			$jobQueueRunner->run();
		}

		$this->testEnvironment->executePendingDeferredUpdates();
	}

	private function doRunParserTests( $jsonTestCaseFileHandler ) {

		$this->parserTestCaseProcessor->setDebugMode(
			$jsonTestCaseFileHandler->getDebugMode()
		);

		foreach ( $jsonTestCaseFileHandler->findTestCasesByType( 'parser' ) as $case ) {

			if ( $jsonTestCaseFileHandler->requiredToSkipFor( $case, $this->connectorId ) ) {
				continue;
			}

			$this->parserTestCaseProcessor->process( $case );
		}
	}

	private function doRunSpecialTests( $jsonTestCaseFileHandler ) {

		$this->specialPageTestCaseProcessor->setDebugMode(
			$jsonTestCaseFileHandler->getDebugMode()
		);

		foreach ( $jsonTestCaseFileHandler->findTestCasesByType( 'special' ) as $case ) {

			if ( $jsonTestCaseFileHandler->requiredToSkipFor( $case, $this->connectorId ) ) {
				continue;
			}

			$this->specialPageTestCaseProcessor->process( $case );
		}
	}

	private function doRunRdfTests( $jsonTestCaseFileHandler ) {

		// For some reason there are some random failures where
		// the instance hasn't reset the cache in time to fetch the
		// property definition which only happens for the SPARQLStore

		// This should not be necessary because the resetcache event
		// is triggered`
		$this->eventDispatcher->dispatch( 'exporter.reset' );
		$this->eventDispatcher->dispatch( 'query.comparator.reset' );

		$this->rdfTestCaseProcessor->setDebugMode(
			$jsonTestCaseFileHandler->getDebugMode()
		);

		foreach ( $jsonTestCaseFileHandler->findTestCasesByType( 'rdf' ) as $case ) {
			$this->rdfTestCaseProcessor->process( $case );
		}
	}

	private function doRunQueryTests( $jsonTestCaseFileHandler ) {

		// Set query parser late to ensure that expected settings are adjusted
		// (language etc.) because the __construct relies on the context language
		$this->queryTestCaseProcessor->setQueryParser(
			ApplicationFactory::getInstance()->getQueryFactory()->newQueryParser()
		);

		$this->queryTestCaseProcessor->setDebugMode(
			$jsonTestCaseFileHandler->getDebugMode()
		);

		foreach ( $jsonTestCaseFileHandler->findTestCasesByType( 'query' ) as $case ) {

			if ( $jsonTestCaseFileHandler->requiredToSkipFor( $case, $this->connectorId ) ) {
				continue;
			}

			$this->queryTestCaseProcessor->processQueryCase( new QueryTestCaseInterpreter( $case ) );
		}

		foreach ( $jsonTestCaseFileHandler->findTestCasesByType( 'concept' ) as $conceptCase ) {
			$this->queryTestCaseProcessor->processConceptCase( new QueryTestCaseInterpreter( $conceptCase ) );
		}

		foreach ( $jsonTestCaseFileHandler->findTestCasesByType( 'format' ) as $case ) {

			if ( $jsonTestCaseFileHandler->requiredToSkipFor( $case, $this->connectorId ) ) {
				continue;
			}

			$this->queryTestCaseProcessor->processFormatCase( new QueryTestCaseInterpreter( $case ) );
		}
	}

}
