<?php

namespace SMW;

/**
 * Encapsulate Semantic MediaWiki settings
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @note Initial idea has been borrowed from EducationProgram Extension/Jeroen De Dauw
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * Encapsulate Semantic MediaWiki settings to access values through a
 * specified interface
 *
 * @ingroup SMW
 */
class Settings extends ArrayAccessor {

	/** @var Settings */
	private static $instance = null;

	/**
	 * Assemble individual SMW related settings into one accessible array for
	 * easy instantiation since we don't have unique way of accessing only
	 * SMW related settings ( e.g. $smwgSettings['...']) we need this method
	 * as short cut to invoke only smwg* related settings
	 *
	 * @par Example:
	 * @code
	 *  $settings = \SMW\Settings::newFromGlobals();
	 *
	 *  $settings->get( 'smwgDefaultStore' );
	 * @endcode
	 *
	 * @since 1.9
	 *
	 * @return Settings
	 */
	public static function newFromGlobals() {

		$settings = array(
			'smwgScriptPath' => $GLOBALS['smwgScriptPath'],
			'smwgIP' => $GLOBALS['smwgIP'],
			'smwgDefaultStore' => $GLOBALS['smwgDefaultStore'],
			'smwgSparqlDatabase' => $GLOBALS['smwgSparqlDatabase'],
			'smwgSparqlQueryEndpoint' => $GLOBALS['smwgSparqlQueryEndpoint'],
			'smwgSparqlUpdateEndpoint' => $GLOBALS['smwgSparqlUpdateEndpoint'],
			'smwgSparqlDataEndpoint' => $GLOBALS['smwgSparqlDataEndpoint'],
			'smwgSparqlDefaultGraph' => $GLOBALS['smwgSparqlDefaultGraph'],
			'smwgHistoricTypeNamespace' => $GLOBALS['smwgHistoricTypeNamespace'],
			'smwgNamespaceIndex' => $GLOBALS['smwgNamespaceIndex'],
			'smwgShowFactbox' => $GLOBALS['smwgShowFactbox'],
			'smwgShowFactboxEdit' => $GLOBALS['smwgShowFactboxEdit'],
			'smwgToolboxBrowseLink' => $GLOBALS['smwgToolboxBrowseLink'],
			'smwgInlineErrors' => $GLOBALS['smwgInlineErrors'],
			'smwgUseCategoryHierarchy' => $GLOBALS['smwgUseCategoryHierarchy'],
			'smwgCategoriesAsInstances' => $GLOBALS['smwgCategoriesAsInstances'],
			'smwgLinksInValues' => $GLOBALS['smwgLinksInValues'],
			'smwgDefaultNumRecurringEvents' => $GLOBALS['smwgDefaultNumRecurringEvents'],
			'smwgMaxNumRecurringEvents' => $GLOBALS['smwgMaxNumRecurringEvents'],
			'smwgBrowseShowInverse' => $GLOBALS['smwgBrowseShowInverse'],
			'smwgBrowseShowAll' => $GLOBALS['smwgBrowseShowAll'],
			'smwgSearchByPropertyFuzzy' => $GLOBALS['smwgSearchByPropertyFuzzy'],
			'smwgTypePagingLimit' => $GLOBALS['smwgTypePagingLimit'],
			'smwgConceptPagingLimit' => $GLOBALS['smwgConceptPagingLimit'],
			'smwgPropertyPagingLimit' => $GLOBALS['smwgPropertyPagingLimit'],
			'smwgQEnabled' => $GLOBALS['smwgQEnabled'],
			'smwgQMaxLimit' => $GLOBALS['smwgQMaxLimit'],
			'smwgIgnoreQueryErrors' => $GLOBALS['smwgIgnoreQueryErrors'],
			'smwgQSubcategoryDepth' => $GLOBALS['smwgQSubcategoryDepth'],
			'smwgQEqualitySupport' => $GLOBALS['smwgQEqualitySupport'],
			'smwgQSortingSupport' => $GLOBALS['smwgQSortingSupport'],
			'smwgQRandSortingSupport' => $GLOBALS['smwgQRandSortingSupport'],
			'smwgQDefaultNamespaces' => $GLOBALS['smwgQDefaultNamespaces'],
			'smwgQComparators' => $GLOBALS['smwgQComparators'],
			'smwStrictComparators' => $GLOBALS['smwStrictComparators'],
			'smwgQMaxSize' => $GLOBALS['smwgQMaxSize'],
			'smwgQMaxDepth' => $GLOBALS['smwgQMaxDepth'],
			'smwgQFeatures' => $GLOBALS['smwgQFeatures'],
			'smwgQDefaultLimit' => $GLOBALS['smwgQDefaultLimit'],
			'smwgQMaxInlineLimit' => $GLOBALS['smwgQMaxInlineLimit'],
			'smwgQPrintoutLimit' => $GLOBALS['smwgQPrintoutLimit'],
			'smwgQDefaultLinking' => $GLOBALS['smwgQDefaultLinking'],
			'smwgQConceptCaching' => $GLOBALS['smwgQConceptCaching'],
			'smwgQConceptMaxSize' => $GLOBALS['smwgQConceptMaxSize'],
			'smwgQConceptMaxDepth' => $GLOBALS['smwgQConceptMaxDepth'],
			'smwgQConceptFeatures' => $GLOBALS['smwgQConceptFeatures'],
			'smwgQConceptCacheLifetime' => $GLOBALS['smwgQConceptCacheLifetime'],
			'smwgResultFormats' => $GLOBALS['smwgResultFormats'],
			'smwgResultAliases' => $GLOBALS['smwgResultAliases'],
			'smwgQuerySources' => $GLOBALS['smwgQuerySources'],
			'smwgPDefaultType' => $GLOBALS['smwgPDefaultType'],
			'smwgAllowRecursiveExport' => $GLOBALS['smwgAllowRecursiveExport'],
			'smwgExportBacklinks' => $GLOBALS['smwgExportBacklinks'],
			'smwgMaxNonExpNumber' => $GLOBALS['smwgMaxNonExpNumber'],
			'smwgEnableUpdateJobs' => $GLOBALS['smwgEnableUpdateJobs'],
			'smwgNamespacesWithSemanticLinks' => $GLOBALS['smwgNamespacesWithSemanticLinks'],
			'smwgPageSpecialProperties' => $GLOBALS['smwgPageSpecialProperties'],
			'smwgDeclarationProperties' => $GLOBALS['smwgDeclarationProperties'],
			'smwgTranslate' => $GLOBALS['smwgTranslate'],
			'smwgAdminRefreshStore' => $GLOBALS['smwgAdminRefreshStore'],
			'smwgAutocompleteInSpecialAsk' => $GLOBALS['smwgAutocompleteInSpecialAsk'],
			'smwgAutoRefreshSubject' => $GLOBALS['smwgAutoRefreshSubject'],
			'smwgAutoRefreshOnPurge' => $GLOBALS['smwgAutoRefreshOnPurge'],
			'smwgAutoRefreshOnPageMove' => $GLOBALS['smwgAutoRefreshOnPageMove'],
			'smwgContLang' => $GLOBALS['smwgContLang'],
			'smwgMaxPropertyValues' => $GLOBALS['smwgMaxPropertyValues'],
			'smwgQSubpropertyDepth' => $GLOBALS['smwgQSubpropertyDepth'],
			'smwgNamespace' => $GLOBALS['smwgNamespace'],
			'smwgMasterStore' => $GLOBALS['smwgMasterStore'],
			'smwgIQRunningNumber' => $GLOBALS['smwgIQRunningNumber'],
			'smwgCacheType' => $GLOBALS['smwgCacheType'],
			'smwgCacheUsage' => $GLOBALS['smwgCacheUsage'],
			'smwgFixedProperties' => $GLOBALS['smwgFixedProperties'],
			'smwgPropertyLowUsageThreshold' => $GLOBALS['smwgPropertyLowUsageThreshold'],
			'smwgPropertyZeroCountDisplay' => $GLOBALS['smwgPropertyZeroCountDisplay'],
		);

		if ( self::$instance === null ) {
			self::$instance = self::newFromArray( $settings ) ;
		}

		return self::$instance;
	}

	/**
	 * Factory method for immediate instantiation of a settings object for a
	 * given array
	 *
	 * @par Example:
	 * @code
	 *  $settings = \SMW\Settings::newFromArray( array( 'Foo' => 'Bar' ) );
	 *
	 *  $settings->get( 'Foo' );
	 * @endcode
	 *
	 * @since 1.9
	 *
	 * @return Settings
	 */
	public static function newFromArray( array $settings ) {
		return new self( $settings );
	}

	/**
	 * Returns settings for a given key (nested settings are supported)
	 *
	 * @par Example:
	 * @code
	 *  $settings = \SMW\Settings::newFromArray( array(
	 *   'Foo' => 'Bar'
	 *   'Parent' => array(
	 *     'Child' => array( 'Lisa', 'Lula', array( 'Lila' ) )
	 *   )
	 *  );
	 *
	 *  $settings->get( 'Child' ) will return array( 'Lisa', 'Lula', array( 'Lila' ) )
	 * @endcode
	 *
	 * @since 1.9
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get( $key ) {

		if ( !$this->has( $key ) ) {

			// If the key wasn't found it could be because of a nested array
			// therefore iterate and verify otherwise throw an exception
			$value = $this->doIterate( $key );
			if ( $value !== null ) {
				return $value;
			}

			throw new InvalidSettingsArgumentException( "'{$key}' is not a valid settings key" );
		}

		return $this->offsetGet( $key );
	}

	/**
	 * Reset the instance
	 *
	 * @since 1.9
	 */
	public static function clear() {
		self::$instance = null;
	}

	/**
	 * Iterate over nested array to find its value for a given key
	 *
	 * @since 1.9
	 *
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	private function doIterate( $key ) {

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveArrayIterator( $this ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach( $iterator as $it => $value ) {
			if ( $key === $it ) {
				return $value;
			}
		}

		return null;
	}
}
