<?php

namespace SMW;

use OutputPage;
use Title;
use Skin;

/**
 * SkinAfterContent hook
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * SkinAfterContent hook to add text after the page content and
 * article metadata
 *
 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinAfterContent
 *
 * @note This hook is used for inserting the Factbox text after the
 * article contents (including categories).
 *
 * @ingroup Hook
 */
class SkinAfterContent extends FunctionHook {

	/** @var OutputPage */
	protected $data = null;

	/** @var ParserOutput */
	protected $skin = null;

	/**
	 * @since  1.9
	 *
	 * @param string $data
	 * @param Skin|null $skin
	 */
	public function __construct( &$data, Skin $skin = null ) {
		$this->data =& $data;
		$this->skin = $skin;
	}

	/**
	 * @see FunctionHook::process
	 *
	 * @note Check invoked action to avoid having the factbox being displayed
	 * in "edit" mode where facts are likly being outdated due to the editing
	 * process
	 *
	 * @since 1.9
	 *
	 * @return true
	 */
	public function process() {
		return $this->skin->getContext()->getRequest()->getVal( 'action' ) !== 'edit' ? $this->performUpdate( $this->skin->getOutput() ) : true;
	}

	/**
	 * @since 1.9
	 *
	 * @return true
	 */
	protected function performUpdate( OutputPage $outputPage ) {

		/**
		 * @var FactboxPresenter $presenter
		 */
		$presenter = $this->getDependencyBuilder()->newObject( 'FactboxPresenter', array(
			'OutputPage' => $outputPage
		) );

		$this->data .= $presenter->retrieveContent();

		return true;
	}

}
