<?php

GFForms::include_feed_addon_framework();

/**
 * Gravity Forms Flexmail Add-On.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    AppSaloon
 * @copyright Copyright (c) 2017, AppSaloon
 */
class GFFlexmail extends GFFeedAddOn {

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  3.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the Flexmail Add-On.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string $_version Contains the version, defined from flexmail.php
	 */
	protected $_version = GF_FLEXMAIL_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '1.9.12';

	/**
	 * Defines the plugin slug.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformsflexmail';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformsflexmail/flexmail.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'https://www.appsaloon.be';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = 'Gravity Forms Flexmail Add-On';

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'Flexmail';

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    bool
	 */
	protected $_enable_rg_autoupgrade = false;

	/**
	 * Defines the capabilities needed for the Flexmail Add-On
	 *
	 * @since  3.0
	 * @access protected
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array( 'gravityforms_flexmail', 'gravityforms_flexmail_uninstall' );

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_flexmail';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_flexmail';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_flexmail_uninstall';

	/**
	 * Defines the Flexmail list field tag name.
	 *
	 * @since  3.7
	 * @access protected
	 * @var    string $merge_var_name The Flexmail list field tag name; used by gform_flexmail_field_value.
	 */
	protected $merge_var_name = '';

	/**
	 * Contains an instance of the Flexmail API library, if available.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    object $api If available, contains an instance of the Flexmail API library.
	 */
	private $api = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFFlexmail
	 * @since  3.0
	 * @access public
	 *
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Autoload the required libraries.
	 *
	 * @since  4.0
	 * @access public
	 *
	 * @uses GFAddOn::is_gravityforms_supported()
	 */
	public function pre_init() {

		parent::pre_init();

		if ( $this->is_gravityforms_supported() ) {

			// Load the Mailgun API library.
			if ( ! class_exists( 'FlexmailAPI' ) ) {
				require_once( 'flexmail-wrapper/FlexmailAPI.php' );
			}

		}

	}

	/**
	 * Plugin starting point. Handles hooks, loading of language files and PayPal delayed payment support.
	 *
	 * @since  3.0
	 * @access public
	 *
	 * @uses GFFeedAddOn::add_delayed_payment_support()
	 */
	public function init() {

		parent::init();

		$this->add_delayed_payment_support(
			array(
				'option_label' => esc_html__( 'Subscribe user to Flexmail only when payment is received.',
					'gravityformsflexmail' ),
			)
		);

	}

	/**
	 * Remove unneeded settings.
	 *
	 * @since  4.0
	 * @access public
	 */
	public function uninstall() {

		parent::uninstall();

		GFCache::delete( 'flexmail_plugin_settings' );
		delete_option( 'gf_flexmail_settings' );
		delete_option( 'gf_flexmail_version' );

	}

	/**
	 * Register needed styles.
	 *
	 * @return array
	 * @since  4.0
	 * @access public
	 *
	 */
	public function styles() {

		$styles = array(
			array(
				'handle'  => $this->_slug . '_form_settings',
				'src'     => $this->get_base_url() . '/css/form_settings.css',
				'version' => $this->_version,
				'enqueue' => array( 'admin_page' => array( 'form_settings' ) ),
			),
		);

		return array_merge( parent::styles(), $styles );

	}





	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 * @since  3.0
	 * @access public
	 *
	 */
	public function plugin_settings_fields() {

		return array(
			array(
				'description' => '<p>' .
				                 sprintf(
					                 esc_html__( 'Flexmail makes it easy to send email newsletters to your customers, manage your subscriber lists, and track campaign performance. Use Gravity Forms to collect customer information and automatically add it to your Flexmail subscriber list. If you don\'t have a Flexmail account, you can %1$ssign up for one here.%2$s',
						                 'gravityformsflexmail' ),
					                 '<a href="http://www.flexmail.com/" target="_blank">', '</a>'
				                 )
				                 . '</p>',
				'fields'      => array(
					array(
						'name'              => 'apiUser',
						'label'             => esc_html__( 'Flexmail User', 'gravityformsflexmail' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'initialize_api' ),
					),
					array(
						'name'              => 'apiKey',
						'label'             => esc_html__( 'Flexmail API Key', 'gravityformsflexmail' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'initialize_api' ),
					),
				),
			),
		);

	}





	// # FEED SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Configures the settings which should be rendered on the feed edit page.
	 *
	 * @return array
	 * @since  3.0
	 * @access public
	 *
	 */
	public function feed_settings_fields() {

		return array(
			array(
				'title'  => esc_html__( 'Flexmail Feed Settings', 'gravityformsflexmail' ),
				'fields' => array(
					array(
						'name'     => 'feedName',
						'label'    => esc_html__( 'Name', 'gravityformsflexmail' ),
						'type'     => 'text',
						'required' => true,
						'class'    => 'medium',
						'tooltip'  => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Name', 'gravityformsflexmail' ),
							esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gravityformsflexmail' )
						),
					),
					array(
						'name'     => 'flexmailCategories',
						'label'    => esc_html__( 'Flexmail Category', 'gravityformsflexmail' ),
						'type'     => 'flexmail_category',
						'required' => true,
						'tooltip'  => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Flexmail Category', 'gravityformsflexmail' ),
							esc_html__( 'Select the Flexmail category.', 'gravityformsflexmail' )
						),
					),
				),
			),
			array(
				'dependency' => 'flexmailCategories',
				'fields'     => array(
					array(
						'name'     => 'flexmailList',
						'label'    => esc_html__( 'Flexmail List', 'gravityformsflexmail' ),
						'type'     => 'flexmail_list',
						'required' => true,
						'tooltip'  => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Flexmail List', 'gravityformsflexmail' ),
							esc_html__( 'Select the Flexmail list you would like to add your contacts to.',
								'gravityformsflexmail' )
						),
					),
				),
			),
			array(
				'dependency' => 'flexmailList',
				'fields'     => array(
					array(
						'name'      => 'mappedFields',
						'label'     => esc_html__( 'Map Fields', 'gravityformsflexmail' ),
						'type'      => 'field_map',
						'field_map' => $this->merge_vars_field_map(),
						'tooltip'   => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Map Fields', 'gravityformsflexmail' ),
							esc_html__( 'Associate your Flexmail merge tags to the appropriate Gravity Form fields by selecting the appropriate form field from the list.',
								'gravityformsflexmail' )
						),
					),
//					array(
//						'name'  => 'note',
//						'type'  => 'textarea',
//						'class' => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
//						'label' => esc_html__( 'Note', 'gravityformsflexmail' ),
//					),
					array(
						'name'    => 'optinCondition',
						'label'   => esc_html__( 'Conditional Logic', 'gravityformsflexmail' ),
						'type'    => 'feed_condition',
						'tooltip' => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Conditional Logic', 'gravityformsflexmail' ),
							esc_html__( 'When conditional logic is enabled, form submissions will only be exported to Flexmail when the conditions are met. When disabled all form submissions will be exported.',
								'gravityformsflexmail' )
						),
					),
					array( 'type' => 'save' ),
				),
			),
		);

	}

	/**
	 * Define the markup for the flexmail_list type field.
	 *
	 * @param  array  $field  The field properties.
	 * @param  bool  $echo  Should the setting markup be echoed. Defaults to true.
	 *
	 * @return string
	 * @since  3.0
	 * @access public
	 *
	 */
	public function settings_flexmail_category( $field, $echo = true ) {

		// Initialize HTML string.
		$html = '';

		// If API is not initialized, return.
		if ( ! $this->initialize_api() ) {
			return $html;
		}

		// Prepare list request parameters.
		$params = array( 'start' => 0, 'limit' => 100 );

		// Filter parameters.
		$params = apply_filters( 'gform_flexmail_lists_params', $params );

		// Convert start parameter to 3.0.
		if ( isset( $params['start'] ) ) {
			$params['offset'] = $params['start'];
			unset( $params['start'] );
		}

		// Convert limit parameter to 3.0.
		if ( isset( $params['limit'] ) ) {
			$params['count'] = $params['limit'];
			unset( $params['limit'] );
		}

		try {

			// Log contact lists request parameters.
			$this->log_debug( __METHOD__ . '(): Retrieving contact lists; params: ' . print_r( $params, true ) );

			// Get lists.
			$category_api = new FlexmailAPI_Category();
			$category     = $category_api->getAll();

		} catch ( Exception $e ) {

			// Log that contact lists could not be obtained.
			$this->log_error( __METHOD__ . '(): Could not retrieve Flexmail contact lists; ' . $e->getMessage() );

			// Display error message.
			printf( esc_html__( 'Could not load Flexmail contact lists. %sError: %s', 'gravityformsflexmail' ), '<br/>',
				$e->getMessage() );

			return;

		}

		// If no lists were found, display error message.
		if ( 0 === sizeof( $category->categoryTypeItems ) ) {

			// Log that no lists were found.
			$this->log_error( __METHOD__ . '(): Could not load Flexmail contact lists; no categories found.' );

			// Display error message.
			printf( esc_html__( 'Could not load Flexmail categories. %sError: %s', 'gravityformsflexmail' ), '<br/>',
				esc_html__( 'No categories found.', 'gravityformsflexmail' ) );

			return;

		}

		// Log number of lists retrieved.
		$this->log_debug( __METHOD__ . '(): Number of lists: ' . count( $category->categoryTypeItems ) );

		// Initialize select options.
		$options = array(
			array(
				'label' => esc_html__( 'Select a Flexmail categories', 'gravityformsflexmail' ),
				'value' => '',
			),
		);

		// Loop through Flexmail lists.
		foreach ( $category->categoryTypeItems as $cat ) {

			// Add list to select options.
			$options[] = array(
				'label' => esc_html( $cat->categoryName ),
				'value' => esc_attr( $cat->categoryId ),
			);

		}

		// Add select field properties.
		$field['type']     = 'select';
		$field['choices']  = $options;
		$field['onchange'] = 'jQuery(this).parents("form").submit();';

		// Generate select field.
		$html = $this->settings_select( $field, false );

		if ( $echo ) {
			echo $html;
		}

		return $html;

	}

	/**
	 * Define the markup for the flexmail_list type field.
	 *
	 * @param  array  $field  The field properties.
	 * @param  bool  $echo  Should the setting markup be echoed. Defaults to true.
	 *
	 * @return string
	 * @since  3.0
	 * @access public
	 *
	 */
	public function settings_flexmail_list( $field, $echo = true ) {

		// Initialize HTML string.
		$html = '';

		// If API is not initialized, return.
		if ( ! $this->initialize_api() ) {
			return $html;
		}

		// Prepare list request parameters.
		$params = array( 'start' => 0, 'limit' => 100 );

		// Filter parameters.
		$params = apply_filters( 'gform_flexmail_lists_params', $params );

		// Convert start parameter to 3.0.
		if ( isset( $params['start'] ) ) {
			$params['offset'] = $params['start'];
			unset( $params['start'] );
		}

		// Convert limit parameter to 3.0.
		if ( isset( $params['limit'] ) ) {
			$params['count'] = $params['limit'];
			unset( $params['limit'] );
		}

		try {

			// Log contact lists request parameters.
			$this->log_debug( __METHOD__ . '(): Retrieving contact lists; params: ' . print_r( $params, true ) );

			$flex_category_id = $this->get_setting( 'flexmailCategories' );

			$list_api = new FlexmailAPI_List();
			$lists    = $list_api->getAll( array( 'categoryId' => $flex_category_id ) );

			if ( $lists->header->errorCode !== 0 ) {
				// Log that contact lists could not be obtained.
				$this->log_error( __METHOD__ . '(): Could not retrieve Flexmail contact lists; ' . $lists->header->errorMessage );

				// Display error message.
				printf( esc_html__( 'Could not load Flexmail contact lists. %sError: %s', 'gravityformsflexmail' ),
					'<br/>', $lists->header->errorMessage );

				return;
			}

		} catch ( Exception $e ) {

			// Log that contact lists could not be obtained.
			$this->log_error( __METHOD__ . '(): Could not retrieve Flexmail contact lists; ' . $e->getMessage() );

			// Display error message.
			printf( esc_html__( 'Could not load Flexmail contact lists. %sError: %s', 'gravityformsflexmail' ), '<br/>',
				$e->getMessage() );

			return;

		}

		// If no lists were found, display error message.
		if ( 0 === sizeof( $lists->mailingListTypeItems ) ) {

			// Log that no lists were found.
			$this->log_error( __METHOD__ . '(): Could not load Flexmail contact lists; no lists found.' );

			// Display error message.
			printf( esc_html__( 'Could not load Flexmail contact lists. %sError: %s', 'gravityformsflexmail' ), '<br/>',
				esc_html__( 'No lists found.', 'gravityformsflexmail' ) );

			return;

		}

		// Log number of lists retrieved.
		$this->log_debug( __METHOD__ . '(): Number of lists: ' . count( $lists->mailingListTypeItems ) );

		// Initialize select options.
		$options = array(
			array(
				'label' => esc_html__( 'Select a Flexmail List', 'gravityformsflexmail' ),
				'value' => '',
			),
		);

		// Loop through Flexmail lists.
		foreach ( $lists->mailingListTypeItems as $list ) {

			// Add list to select options.
			$options[] = array(
				'label' => esc_html( $list->mailingListName ),
				'value' => esc_attr( $list->mailingListId ),
			);

		}

		// Add select field properties.
		$field['type']     = 'select';
		$field['choices']  = $options;
		$field['onchange'] = 'jQuery(this).parents("form").submit();';

		// Generate select field.
		$html = $this->settings_select( $field, false );

		if ( $echo ) {
			echo $html;
		}

		return $html;

	}

	/**
	 * Return an array of Flexmail list fields which can be mapped to the Form fields/entry meta.
	 *
	 * @return array
	 * @since  3.0
	 * @access public
	 *
	 */
	public function merge_vars_field_map() {

		// Initialize field map array.
		$field_map = array(
			'EMAIL' => array(
				'name'       => 'EMAIL',
				'label'      => esc_html__( 'Email Address', 'gravityformsmailchimp' ),
				'required'   => true,
				'field_type' => array( 'email', 'hidden' ),
			),
		);

		// If unable to initialize API, return field map.
		if ( ! $this->initialize_api() ) {
			return $field_map;
		}

		$merge_fields   = array();
		$merge_fields[] = array( 'tag'      => 'title',
		                         'name'     => __( 'Title', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'name',
		                         'name'     => __( 'Name', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'surname',
		                         'name'     => __( 'Surname', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'address',
		                         'name'     => __( 'Address', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => 'address',
		);
		$merge_fields[] = array( 'tag'      => 'zipcode',
		                         'name'     => __( 'Zipcode', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => 'address',
		);
		$merge_fields[] = array( 'tag'      => 'city',
		                         'name'     => __( 'City', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => 'address',
		);
		$merge_fields[] = array( 'tag'      => 'country',
		                         'name'     => __( 'Country', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => 'address',
		);
		$merge_fields[] = array( 'tag'      => 'phone',
		                         'name'     => __( 'Phone', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'fax',
		                         'name'     => __( 'Fax', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'mobile',
		                         'name'     => __( 'Mobile', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'website',
		                         'name'     => __( 'Website', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'language',
		                         'name'     => __( 'Language', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'gender',
		                         'name'     => __( 'Gender', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'birthday',
		                         'name'     => __( 'Birthday', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'company',
		                         'name'     => __( 'Company', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'function',
		                         'name'     => __( 'Function', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'market',
		                         'name'     => __( 'Market', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'employees',
		                         'name'     => __( 'Employees', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'nace',
		                         'name'     => __( 'Nace', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'turnover',
		                         'name'     => __( 'Turnover', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'vat',
		                         'name'     => __( 'Vat', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'keywords',
		                         'name'     => __( 'Keywords', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'free_field_1',
		                         'name'     => __( 'free_field_1', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'free_field_2',
		                         'name'     => __( 'free_field_2', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'free_field_3',
		                         'name'     => __( 'free_field_3', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'free_field_4',
		                         'name'     => __( 'free_field_4', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'free_field_5',
		                         'name'     => __( 'free_field_5', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'free_field_6',
		                         'name'     => __( 'free_field_6', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);
		$merge_fields[] = array( 'tag'      => 'barcode',
		                         'name'     => __( 'Barcode', 'gravityformsmailchimp' ),
		                         'required' => false,
		                         'type'     => null,
		);

		// If merge fields exist, add to field map.
		if ( ! empty( $merge_fields ) ) {

			// Loop through merge fields.
			foreach ( $merge_fields as $merge_field ) {

				// Define required field type.
				$field_type = null;

				// If this is an email merge field, set field types to "email" or "hidden".
				if ( 'EMAIL' === strtoupper( $merge_field['tag'] ) ) {
					$field_type = array( 'email', 'hidden' );
				}

				// If this is an address merge field, set field type to "address".
				if ( 'address' === $merge_field['type'] ) {
					$field_type = array( 'address' );
				}

				// Add to field map.
				$field_map[ $merge_field['tag'] ] = array(
					'name'       => $merge_field['tag'],
					'label'      => $merge_field['name'],
					'required'   => $merge_field['required'],
					'field_type' => $field_type,
				);

			}

		}

		return $field_map;
	}

	/**
	 * Prevent feeds being listed or created if the API key isn't valid.
	 *
	 * @return bool
	 * @since  3.0
	 * @access public
	 *
	 */
	public function can_create_feed() {

		return $this->initialize_api();

	}

	/**
	 * Configures which columns should be displayed on the feed list page.
	 *
	 * @return array
	 * @since  3.0
	 * @access public
	 *
	 */
	public function feed_list_columns() {

		return array(
			'feedName' => esc_html__( 'Name', 'gravityformsflexmail' ),
		);

	}

	/**
	 * Define which field types can be used for the group conditional logic.
	 *
	 * @return array
	 * @uses GFAddOn::get_current_form()
	 * @uses GFCommon::get_label()
	 * @uses GF_Field::get_entry_inputs()
	 * @uses GF_Field::get_input_type()
	 * @uses GF_Field::is_conditional_logic_supported()
	 *
	 * @since  3.0
	 * @access public
	 *
	 */
	public function get_conditional_logic_fields() {

		// Initialize conditional logic fields array.
		$fields = array();

		// Get the current form.
		$form = $this->get_current_form();

		// Loop through the form fields.
		foreach ( $form['fields'] as $field ) {

			// If this field does not support conditional logic, skip it.
			if ( ! $field->is_conditional_logic_supported() ) {
				continue;
			}

			// Get field inputs.
			$inputs = $field->get_entry_inputs();

			// If field has multiple inputs, add them as individual field options.
			if ( $inputs && 'checkbox' !== $field->get_input_type() ) {

				// Loop through the inputs.
				foreach ( $inputs as $input ) {

					// If this is a hidden input, skip it.
					if ( rgar( $input, 'isHidden' ) ) {
						continue;
					}

					// Add input to conditional logic fields array.
					$fields[] = array(
						'value' => $input['id'],
						'label' => GFCommon::get_label( $field, $input['id'] ),
					);

				}

			} else {

				// Add field to conditional logic fields array.
				$fields[] = array(
					'value' => $field->id,
					'label' => GFCommon::get_label( $field ),
				);

			}

		}

		return $fields;

	}



	// # FEED PROCESSING -----------------------------------------------------------------------------------------------

	/**
	 * Process the feed, subscribe the user to the list.
	 *
	 * @param  array  $feed  The feed object to be processed.
	 * @param  array  $entry  The entry object currently being processed.
	 * @param  array  $form  The form object currently being processed.
	 *
	 * @return array
	 * @since  3.0
	 * @access public
	 *
	 */
	public function process_feed( $feed, $entry, $form ) {
		// Log that we are processing feed.
		$this->log_debug( __METHOD__ . '(): Processing feed.' );

		// If unable to initialize API, log error and return.
		if ( ! $this->initialize_api() ) {
			$this->add_feed_error( esc_html__( 'Unable to process feed because API could not be initialized.',
				'gravityformsflexmail' ), $feed, $entry, $form );

			return $entry;
		}

		// Set current merge variable name.
		$this->merge_var_name = 'EMAIL';

		// Get field map values.
		$field_map = $this->get_field_map_fields( $feed, 'mappedFields' );

		// Get mapped email address.
		$email = $this->get_field_value( $form, $entry, $field_map['EMAIL'] );

		// If email address is invalid, log error and return.
		if ( GFCommon::is_invalid_or_empty_email( $email ) ) {
			$this->add_feed_error( esc_html__( 'A valid Email address must be provided.', 'gravityformsflexmail' ),
				$feed, $entry, $form );

			return $entry;
		}

		/**
		 * Prevent empty form fields erasing values already stored in the mapped Flexmail MMERGE fields
		 * when updating an existing subscriber.
		 *
		 * @param  bool  $override  If the merge field should be overridden.
		 * @param  array  $form  The form object.
		 * @param  array  $entry  The entry object.
		 * @param  array  $feed  The feed object.
		 */
		$override_empty_fields = gf_apply_filters( 'gform_flexmail_override_empty_fields', array( $form['id'] ), true,
			$form, $entry, $feed );

		// Log that empty fields will not be overridden.
		if ( ! $override_empty_fields ) {
			$this->log_debug( __METHOD__ . '(): Empty fields will not be overridden.' );
		}

		// Initialize array to store merge vars.
		$merge_vars = array();

		// Loop through field map.
		foreach ( $field_map as $name => $field_id ) {

			// If this is the email field, skip it.
			if ( strtoupper( $name ) === 'EMAIL' ) {
				$name = 'emailAddress';
			}

			// Set merge var name to current field map name.
			$this->merge_var_name = $name;

			// Get field object.
			$field = GFFormsModel::get_field( $form, $field_id );

			// Get field value.
			$field_value = $this->get_field_value( $form, $entry, $field_id );

			// If field value is empty and we are not overriding empty fields, skip it.
			if ( empty( $field_value ) && ( ! $override_empty_fields || ( is_object( $field ) && 'address' === $field->get_input_type() ) ) ) {
				continue;
			}

			if ( ! empty( $field_value ) ) {
				$merge_vars[ $name ] = $field_value;
			}

		}

		// Define initial member found and member status variables.
		$member_found  = false;
		$member_status = null;
		$member_id     = null;

		$contact_api = new FlexmailAPI_Contact();

		// set parameters
		$parameters = array(
			'mailingListIds'        => array( $feed['meta']['flexmailList'] ),
			'emailAddressTypeItems' => array(
				array( 'emailAddress' => $email, 'mailingListid' => $feed['meta']['flexmailList'] ),
			),
		);

		try {
			// Log that we are checking if user is already subscribed to list.
			$this->log_debug( __METHOD__ . "(): Checking to see if $email is already on the list." );

			// Get member info.
			$member = $contact_api->getAll( $parameters );

			// no error code
			if ( $member->header->errorCode === 0 ) {
				// checking emailadres
				if ( sizeof( $member->emailAddressTypeItems ) > 0 ) {
					// Get the first email account
					$emailAddress = current( $member->emailAddressTypeItems );

					// if no error code
					if ( ! isset( $emailAddress->errorCode ) ) {
						// Set member found status to true.
						$member_found = true;

						// Set member status.
						$member_status = $emailAddress->state;

						$member_id = $emailAddress->flexmailId;

						$merge_vars['flexmailId'] = $emailAddress->flexmailId;
					}
				}
			} else {
				throw new Exception( $member->header->errorMessage, $member->header->errorCode );
			}

		} catch ( Exception $e ) {

			// If the exception code is not 404, abort feed processing.
			if ( 404 !== $e->getCode() ) {

				if ( 225 === $e->getCode() ) {
					// Log that we could not get the member information.
					$this->add_feed_error( sprintf( esc_html__( 'Email address is not added to the list: %s',
						'gravityformsflexmail' ), $e->getMessage() ), $feed, $entry, $form );
				} else {
					// Log that we could not get the member information.
					$this->add_feed_error( sprintf( esc_html__( 'Unable to check if email address is already used by a member: %s',
						'gravityformsflexmail' ), $e->getMessage() ), $feed, $entry, $form );
				}

				return $entry;
			}
		}

		// if member status is not active and not null
		if ( 'active' !== $member_status && null !== $member_status ) {
			$this->log_debug( __METHOD__ . '(): User has different status and resubscription is not allowed.' );

			return;
		}

		// Prepare subscription arguments.
		$subscription = array(
			'mailingListId'    => $feed['meta']['flexmailList'],
			'emailAddressType' => $merge_vars,
		);

		// Prepare transaction type for filter.
		$transaction = $member_found ? 'updated' : 'subscribed';

		/**
		 * Modify the subscription object before it is executed.
		 *
		 * @param  array  $subscription  Subscription arguments.
		 * @param  array  $form  The form object.
		 * @param  array  $entry  The entry object.
		 * @param  array  $feed  The feed object.
		 * @param  string  $transaction  Transaction type. Defaults to Subscribe.
		 *
		 * @deprecated 4.0 @use gform_flexmail_subscription
		 *
		 */
		$subscription = gf_apply_filters( array( 'gform_flexmail_args_pre_subscribe', $form['id'] ), $subscription,
			$form, $entry, $feed, $transaction );

		try {
			// Log the subscriber to be added or updated.
			$this->log_debug( __METHOD__ . "(): Subscriber to be {$transaction}: " . print_r( $subscription, true ) );

			if ( $transaction === 'updated' ) {
				$contact_api->update( $subscription );
			} else {
				$contact_api->create( $subscription );
			}

			// Log that the subscription was added or updated.
			$this->log_debug( __METHOD__ . "(): Subscriber successfully {$transaction}." );

		} catch ( Exception $e ) {
			// Log that subscription could not be added or updated.
			$this->add_feed_error( sprintf( esc_html__( 'Unable to add/update subscriber: %s', 'gravityformsflexmail' ),
				$e->getMessage() ), $feed, $entry, $form );


			return;
		}
	}

	/**
	 * Returns the value of the selected field.
	 *
	 * @param  array  $form  The form object currently being processed.
	 * @param  array  $entry  The entry object currently being processed.
	 * @param  string  $field_id  The ID of the field being processed.
	 *
	 * @return array
	 * @since  3.0
	 * @access public
	 *
	 * @uses GFAddOn::get_full_name()
	 * @uses GF_Field::get_value_export()
	 * @uses GFFormsModel::get_field()
	 * @uses GFFormsModel::get_input_type()
	 * @uses GFFlexmail::get_full_address()
	 * @uses GFFlexmail::maybe_override_field_value()
	 *
	 */
	public function get_field_value( $form, $entry, $field_id ) {

		// Set initial field value.
		$field_value = '';

		// Set field value based on field ID.
		switch ( strtolower( $field_id ) ) {

			// Form title.
			case 'form_title':
				$field_value = rgar( $form, 'title' );
				break;

			// Entry creation date.
			case 'date_created':

				// Get entry creation date from entry.
				$date_created = rgar( $entry, strtolower( $field_id ) );

				// If date is not populated, get current date.
				$field_value = empty( $date_created ) ? gmdate( 'Y-m-d H:i:s' ) : $date_created;
				break;

			// Entry IP and source URL.
			case 'ip':
			case 'source_url':
				$field_value = rgar( $entry, strtolower( $field_id ) );
				break;

			default:

				// Get field object.
				$field = GFFormsModel::get_field( $form, $field_id );

				if ( is_object( $field ) ) {

					// Check if field ID is integer to ensure field does not have child inputs.
					$is_integer = $field_id == intval( $field_id );

					// Get field input type.
					$input_type = GFFormsModel::get_input_type( $field );

					if ( $is_integer && 'address' === $input_type ) {

						// Get full address for field value.
						$field_value = $this->get_full_address( $entry, $field_id );

					} elseif ( $is_integer && 'name' === $input_type ) {

						// Get full name for field value.
						$field_value = $this->get_full_name( $entry, $field_id );

					} elseif ( $is_integer && 'checkbox' === $input_type ) {

						// Initialize selected options array.
						$selected = array();

						// Loop through checkbox inputs.
						foreach ( $field->inputs as $input ) {
							$index = (string) $input['id'];
							if ( ! rgempty( $index, $entry ) ) {
								$selected[] = $this->maybe_override_field_value( rgar( $entry, $index ), $form, $entry,
									$index );
							}
						}

						// Convert selected options array to comma separated string.
						$field_value = implode( ', ', $selected );

					} elseif ( 'phone' === $input_type && $field->phoneFormat == 'standard' ) {

						// Get field value.
						$field_value = rgar( $entry, $field_id );

						// Reformat standard format phone to match Flexmail format.
						// Format: NPA-NXX-LINE (404-555-1212) when US/CAN.
						if ( ! empty( $field_value ) && preg_match( '/^\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/',
								$field_value, $matches ) ) {
							$field_value = sprintf( '%s-%s-%s', $matches[1], $matches[2], $matches[3] );
						}

					} else {

						// Use export value if method exists for field.
						if ( is_callable( array( 'GF_Field', 'get_value_export' ) ) ) {
							$field_value = $field->get_value_export( $entry, $field_id );
						} else {
							$field_value = rgar( $entry, $field_id );
						}

					}

				} else {

					// Get field value from entry.
					$field_value = rgar( $entry, $field_id );

				}

		}

		return $this->maybe_override_field_value( $field_value, $form, $entry, $field_id );

	}

	/**
	 * Use the legacy gform_flexmail_field_value filter instead of the framework gform_SLUG_field_value filter.
	 *
	 * @param  string  $field_value  The field value.
	 * @param  array  $form  The form object currently being processed.
	 * @param  array  $entry  The entry object currently being processed.
	 * @param  string  $field_id  The ID of the field being processed.
	 *
	 * @return string
	 * @since  3.0
	 * @access public
	 *
	 */
	public function maybe_override_field_value( $field_value, $form, $entry, $field_id ) {

		return gf_apply_filters( 'gform_flexmail_field_value', array( $form['id'], $field_id ), $field_value,
			$form['id'], $field_id, $entry, $this->merge_var_name );

	}





	// # HELPERS -------------------------------------------------------------------------------------------------------

	/**
	 * Initializes Flexmail API if credentials are valid.
	 *
	 * @param  string  $api_key  Flexmail API key.
	 *
	 * @return bool|null
	 * @uses GFAddOn::get_plugin_setting()
	 * @uses GFAddOn::log_debug()
	 * @uses GFAddOn::log_error()
	 * @uses GF_Flexmail_API::account_details()
	 *
	 * @since  4.0
	 * @access public
	 *
	 */
	public function initialize_api( $api_key = null ) {

		// If API is alredy initialized, return true.
		if ( ! is_null( $this->api ) ) {
			return true;
		}

		// Get the API key.
		if ( rgblank( $api_key ) ) {
			$api_key = $this->get_plugin_setting( 'apiKey' );
		}

		// If the API key is blank, do not run a validation check.
		if ( rgblank( $api_key ) ) {
			return null;
		}

		// Log validation step.
		$this->log_debug( __METHOD__ . '(): Validating API Info.' );

		// Setup a new Flexmail object with the API credentials.
		$mc = new FlexmailAPI_Account();

		try {
			// check user logged in
			$lists = $mc->getBalance();

			if ( $lists->header->errorCode !== 0 ) {
				// Log that authentication test failed.
				$this->log_error( __METHOD__ . '(): Unable to authenticate with Flexmail; ' . $lists->header->errorMessage );

				return false;
			}

			// Log that authentication test passed.
			$this->log_debug( __METHOD__ . '(): Flexmail successfully authenticated.' );

			return true;

		} catch ( Exception $e ) {

			// Log that authentication test failed.
			$this->log_error( __METHOD__ . '(): Unable to authenticate with Flexmail; ' . $e->getMessage() );

			return false;

		}

		die;
	}

	/**
	 * Returns the combined value of the specified Address field.
	 * Street 2 and Country are the only inputs not required by Flexmail.
	 * If other inputs are missing Flexmail will not store the field value, we will pass a hyphen when an input is empty.
	 * Flexmail requires the inputs be delimited by 2 spaces.
	 *
	 * @param  array  $entry  The entry currently being processed.
	 * @param  string  $field_id  The ID of the field to retrieve the value for.
	 *
	 * @return array|null
	 * @since  3.0
	 * @access public
	 *
	 */
	public function get_full_address( $entry, $field_id ) {

		// Initialize address array.
		$address = array(
			'addr1'   => str_replace( '  ', ' ', trim( rgar( $entry, $field_id . '.1' ) ) ),
			'addr2'   => str_replace( '  ', ' ', trim( rgar( $entry, $field_id . '.2' ) ) ),
			'city'    => str_replace( '  ', ' ', trim( rgar( $entry, $field_id . '.3' ) ) ),
			'state'   => str_replace( '  ', ' ', trim( rgar( $entry, $field_id . '.4' ) ) ),
			'zip'     => trim( rgar( $entry, $field_id . '.5' ) ),
			'country' => trim( rgar( $entry, $field_id . '.6' ) ),
		);

		// Get address parts.
		$address_parts = array_values( $address );

		// Remove empty address parts.
		$address_parts = array_filter( $address_parts );

		// If no address parts exist, return null.
		if ( empty( $address_parts ) ) {
			return null;
		}

		// Replace country with country code.
		if ( ! empty( $address['country'] ) ) {
			$address['country'] = GF_Fields::get( 'address' )->get_country_code( $address['country'] );
		}

		return $address;

	}

}