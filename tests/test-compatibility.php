<?php

class WDSRESTCUI_Compatibility_Test extends WP_UnitTestCase {

	function test_sample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function test_class_exists() {
		$this->assertTrue( class_exists( 'WDSRESTCUI_Compatibility') );
	}

	function test_class_access() {
		$this->assertTrue( wds_rest_connect_ui()->compatibility instanceof WDSRESTCUI_Compatibility );
	}
}
