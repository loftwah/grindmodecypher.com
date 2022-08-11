<?php
/**
 * Functions used only while viewing the admin UI.
 *
 * Limit loading these function only when needed
 * and not in the front end.
 *
 * @package OptionTree
 */

if ( ! defined( 'OT_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}

if ( ! function_exists( 'ot_register_theme_options_page' ) ) {

	/**
	 * Registers the Theme Option page
	 *
	 * @uses ot_register_settings()
	 *
	 * @access public
	 * @since  2.1
	 */
	function ot_register_theme_options_page() {

		// Get the settings array.
		$get_settings = get_option( ot_settings_id() );

		// Sections array.
		$sections = isset( $get_settings['sections'] ) ? $get_settings['sections'] : array();

		// Settings array.
		$settings = isset( $get_settings['settings'] ) ? $get_settings['settings'] : array();

		// Contexual help array.
		$contextual_help = isset( $get_settings['contextual_help'] ) ? $get_settings['contextual_help'] : array();

		// Build the Theme Options.
		if ( function_exists( 'ot_register_settings' ) && OT_USE_THEME_OPTIONS ) {

			$caps = apply_filters( 'ot_theme_options_capability', 'edit_theme_options' );

			ot_register_settings(
				array(
					array(
						'id'    => ot_options_id(),
						'pages' => array(
							array(
								'id'              => 'ot_theme_options',
								'parent_slug'     => apply_filters( 'ot_theme_options_parent_slug', 'themes.php' ),
								'page_title'      => apply_filters( 'ot_theme_options_page_title', esc_html__( 'Theme Options', 'hueman' ) ),
								'menu_title'      => apply_filters( 'ot_theme_options_menu_title', esc_html__( 'Theme Options', 'hueman' ) ),
								'capability'      => $caps,
								'menu_slug'       => apply_filters( 'ot_theme_options_menu_slug', 'ot-theme-options' ),
								'icon_url'        => apply_filters( 'ot_theme_options_icon_url', null ),
								'position'        => apply_filters( 'ot_theme_options_position', null ),
								'updated_message' => apply_filters( 'ot_theme_options_updated_message', esc_html__( 'Theme Options updated.', 'hueman' ) ),
								'reset_message'   => apply_filters( 'ot_theme_options_reset_message', esc_html__( 'Theme Options reset.', 'hueman' ) ),
								'button_text'     => apply_filters( 'ot_theme_options_button_text', esc_html__( 'Save Changes', 'hueman' ) ),
								'contextual_help' => apply_filters( 'ot_theme_options_contextual_help', $contextual_help ),
								'sections'        => apply_filters( 'ot_theme_options_sections', $sections ),
								'settings'        => apply_filters( 'ot_theme_options_settings', $settings ),
							),
						),
					),
				)
			);

			// Filters the options.php to add the minimum user capabilities.
			add_filter(
				'option_page_capability_' . ot_options_id(),
				function() use ( $caps ) {
					return $caps;
				},
				999
			);

		}

	}
}

if ( ! function_exists( 'ot_register_settings_page' ) ) {

	/**
	 * Registers the Settings page.
	 *
	 * @access public
	 * @since  2.1
	 */
	function ot_register_settings_page() {
		global $ot_has_custom_theme_options;

		$custom_options = ( true === $ot_has_custom_theme_options || has_action( 'admin_init', 'custom_theme_options' ) || has_action( 'init', 'custom_theme_options' ) );

		// Display UI Builder admin notice.
		if ( true === OT_SHOW_OPTIONS_UI && isset( $_REQUEST['page'] ) && 'ot-settings' === $_REQUEST['page'] && $custom_options ) { // phpcs:ignore

			/**
			 * Error message for custom theme options.
			 */
			function ot_has_custom_theme_options() {
				echo '<div class="error"><p>' . esc_html__( 'The Theme Options UI Builder is being overridden by a custom file in your theme. Any changes you make via the UI Builder will not be saved.', 'hueman' ) . '</p></div>';
			}

			add_action( 'admin_notices', 'ot_has_custom_theme_options' );
		}

		// Create the filterable pages array.
		$ot_register_pages_array = array(
			array(
				'id'          => 'ot',
				'page_title'  => esc_html__( 'OptionTree', 'hueman' ),
				'menu_title'  => esc_html__( 'OptionTree', 'hueman' ),
				'capability'  => 'edit_theme_options',
				'menu_slug'   => 'ot-settings',
				'icon_url'    => null,
				'position'    => 61,
				'hidden_page' => true,
			),
			array(
				'id'              => 'settings',
				'parent_slug'     => 'ot-settings',
				'page_title'      => esc_html__( 'Settings', 'hueman' ),
				'menu_title'      => esc_html__( 'Settings', 'hueman' ),
				'capability'      => 'edit_theme_options',
				'menu_slug'       => 'ot-settings',
				'icon_url'        => null,
				'position'        => null,
				'updated_message' => esc_html__( 'Theme Options updated.', 'hueman' ),
				'reset_message'   => esc_html__( 'Theme Options reset.', 'hueman' ),
				'button_text'     => esc_html__( 'Save Settings', 'hueman' ),
				'show_buttons'    => false,
				'sections'        => array(
					array(
						'id'    => 'create_setting',
						'title' => esc_html__( 'Theme Options UI', 'hueman' ),
					),
					array(
						'id'    => 'import',
						'title' => esc_html__( 'Import', 'hueman' ),
					),
					array(
						'id'    => 'export',
						'title' => esc_html__( 'Export', 'hueman' ),
					),
					array(
						'id'    => 'layouts',
						'title' => esc_html__( 'Layouts', 'hueman' ),
					),
				),
				'settings'        => array(
					array(
						'id'      => 'theme_options_ui_text',
						'label'   => esc_html__( 'Theme Options UI Builder', 'hueman' ),
						'type'    => 'theme_options_ui',
						'section' => 'create_setting',
					),
					array(
						'id'      => 'import_settings_text',
						'label'   => esc_html__( 'Settings', 'hueman' ),
						'type'    => 'import-settings',
						'section' => 'import',
					),
					array(
						'id'      => 'import_data_text',
						'label'   => esc_html__( 'Theme Options', 'hueman' ),
						'type'    => 'import-data',
						'section' => 'import',
					),
					array(
						'id'      => 'import_layouts_text',
						'label'   => esc_html__( 'Layouts', 'hueman' ),
						'type'    => 'import-layouts',
						'section' => 'import',
					),
					array(
						'id'      => 'export_settings_file_text',
						'label'   => esc_html__( 'Settings PHP File', 'hueman' ),
						'type'    => 'export-settings-file',
						'section' => 'export',
					),
					array(
						'id'      => 'export_settings_text',
						'label'   => esc_html__( 'Settings', 'hueman' ),
						'type'    => 'export-settings',
						'section' => 'export',
					),
					array(
						'id'      => 'export_data_text',
						'label'   => esc_html__( 'Theme Options', 'hueman' ),
						'type'    => 'export-data',
						'section' => 'export',
					),
					array(
						'id'      => 'export_layout_text',
						'label'   => esc_html__( 'Layouts', 'hueman' ),
						'type'    => 'export-layouts',
						'section' => 'export',
					),
					array(
						'id'      => 'modify_layouts_text',
						'label'   => esc_html__( 'Layout Management', 'hueman' ),
						'type'    => 'modify-layouts',
						'section' => 'layouts',
					),
				),
			),
			array(
				'id'              => 'documentation',
				'parent_slug'     => 'ot-settings',
				'page_title'      => esc_html__( 'Documentation', 'hueman' ),
				'menu_title'      => esc_html__( 'Documentation', 'hueman' ),
				'capability'      => 'edit_theme_options',
				'menu_slug'       => 'ot-documentation',
				'icon_url'        => null,
				'position'        => null,
				'updated_message' => esc_html__( 'Theme Options updated.', 'hueman' ),
				'reset_message'   => esc_html__( 'Theme Options reset.', 'hueman' ),
				'button_text'     => esc_html__( 'Save Settings', 'hueman' ),
				'show_buttons'    => false,
				'sections'        => array(
					array(
						'id'    => 'creating_options',
						'title' => esc_html__( 'Creating Options', 'hueman' ),
					),
					array(
						'id'    => 'option_types',
						'title' => esc_html__( 'Option Types', 'hueman' ),
					),
					array(
						'id'    => 'functions',
						'title' => esc_html__( 'Function References', 'hueman' ),
					),
					array(
						'id'    => 'theme_mode',
						'title' => esc_html__( 'Theme Mode', 'hueman' ),
					),
					array(
						'id'    => 'meta_boxes',
						'title' => esc_html__( 'Meta Boxes', 'hueman' ),
					),
					array(
						'id'    => 'examples',
						'title' => esc_html__( 'Code Examples', 'hueman' ),
					),
					array(
						'id'    => 'layouts_overview',
						'title' => esc_html__( 'Layouts Overview', 'hueman' ),
					),
				),
				'settings'        => array(
					array(
						'id'      => 'creating_options_text',
						'label'   => esc_html__( 'Overview of available Theme Option fields.', 'hueman' ),
						'type'    => 'creating-options',
						'section' => 'creating_options',
					),
					array(
						'id'      => 'option_types_text',
						'label'   => esc_html__( 'Option types in alphabetical order & hooks to filter them.', 'hueman' ),
						'type'    => 'option-types',
						'section' => 'option_types',
					),
					array(
						'id'      => 'functions_ot_get_option',
						'label'   => esc_html__( 'Function Reference:ot_get_option()', 'hueman' ),
						'type'    => 'ot-get-option',
						'section' => 'functions',
					),
					array(
						'id'      => 'functions_get_option_tree',
						'label'   => esc_html__( 'Function Reference:get_option_tree()', 'hueman' ),
						'type'    => 'get-option-tree',
						'section' => 'functions',
					),
					array(
						'id'      => 'theme_mode_text',
						'label'   => esc_html__( 'Theme Mode', 'hueman' ),
						'type'    => 'theme-mode',
						'section' => 'theme_mode',
					),
					array(
						'id'      => 'meta_boxes_text',
						'label'   => esc_html__( 'Meta Boxes', 'hueman' ),
						'type'    => 'meta-boxes',
						'section' => 'meta_boxes',
					),
					array(
						'id'      => 'example_text',
						'label'   => esc_html__( 'Code examples for front-end development.', 'hueman' ),
						'type'    => 'examples',
						'section' => 'examples',
					),
					array(
						'id'      => 'layouts_overview_text',
						'label'   => esc_html__( 'What\'s a layout anyhow?', 'hueman' ),
						'type'    => 'layouts-overview',
						'section' => 'layouts_overview',
					),
				),
			),
		);

		// Loop over the settings and remove as needed.
		foreach ( $ot_register_pages_array as $key => $page ) {

			// Remove various options from the Settings UI.
			if ( 'settings' === $page['id'] ) {

				// Remove the Theme Options UI.
				if ( false === OT_SHOW_OPTIONS_UI ) {

					foreach ( $page['sections'] as $section_key => $section ) {
						if ( 'create_setting' === $section['id'] ) {
							unset( $ot_register_pages_array[ $key ]['sections'][ $section_key ] );
						}
					}

					foreach ( $page['settings'] as $setting_key => $setting ) {
						if ( 'create_setting' === $setting['section'] ) {
							unset( $ot_register_pages_array[ $key ]['settings'][ $setting_key ] );
						}
					}
				}

				// Remove parts of the Imports UI.
				if ( false === OT_SHOW_SETTINGS_IMPORT ) {

					foreach ( $page['settings'] as $setting_key => $setting ) {
						if ( 'import' === $setting['section'] && in_array( $setting['id'], array( 'import_xml_text', 'import_settings_text' ), true ) ) {
							unset( $ot_register_pages_array[ $key ]['settings'][ $setting_key ] );
						}
					}
				}

				// Remove parts of the Export UI.
				if ( false === OT_SHOW_SETTINGS_EXPORT ) {

					foreach ( $page['settings'] as $setting_key => $setting ) {
						if ( 'export' === $setting['section'] && in_array( $setting['id'], array( 'export_settings_file_text', 'export_settings_text' ), true ) ) {
							unset( $ot_register_pages_array[ $key ]['settings'][ $setting_key ] );
						}
					}
				}

				// Remove the Layouts UI.
				if ( false === OT_SHOW_NEW_LAYOUT ) {

					foreach ( $page['sections'] as $section_key => $section ) {
						if ( 'layouts' === $section['id'] ) {
							unset( $ot_register_pages_array[ $key ]['sections'][ $section_key ] );
						}
					}

					foreach ( $page['settings'] as $setting_key => $setting ) {
						if ( 'layouts' === $setting['section'] ) {
							unset( $ot_register_pages_array[ $key ]['settings'][ $setting_key ] );
						}
					}
				}
			}

			// Remove the Documentation UI.
			if ( false === OT_SHOW_DOCS && 'documentation' === $page['id'] ) {
				unset( $ot_register_pages_array[ $key ] );
			}
		}

		$ot_register_pages_array = apply_filters( 'ot_register_pages_array', $ot_register_pages_array );

		// Register the pages.
		ot_register_settings(
			array(
				array(
					'id'    => ot_settings_id(),
					'pages' => $ot_register_pages_array,
				),
			)
		);

	}
}

if ( ! function_exists( 'ot_after_theme_options_save' ) ) {

	/**
	 * Runs directly after the Theme Options are save.
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_after_theme_options_save() {

		$page    = isset( $_REQUEST['page'] ) ? esc_attr( wp_unslash( $_REQUEST['page'] ) ) : ''; // phpcs:ignore
		$updated = isset( $_REQUEST['settings-updated'] ) && true === filter_var( wp_unslash( $_REQUEST['settings-updated'] ), FILTER_VALIDATE_BOOLEAN ); // phpcs:ignore

		// Only execute after the theme options are saved.
		if ( apply_filters( 'ot_theme_options_menu_slug', 'ot-theme-options' ) === $page && $updated ) {

			// Grab a copy of the theme options.
			$options = get_option( ot_options_id() );

			// Execute the action hook and pass the theme options to it.
			do_action( 'ot_after_theme_options_save', $options );
		}
	}
}

if ( ! function_exists( 'ot_validate_setting' ) ) {

	/**
	 * Validate the options by type before saving.
	 *
	 * This function will run on only some of the option types
	 * as all of them don't need to be validated, just the
	 * ones users are going to input data into; because they
	 * can't be trusted.
	 *
	 * @param  mixed  $input    Setting value.
	 * @param  string $type     Setting type.
	 * @param  string $field_id Setting field ID.
	 * @param  string $wmpl_id  WPML field ID.
	 * @return mixed
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_validate_setting( $input, $type, $field_id, $wmpl_id = '' ) {

		// Exit early if missing data.
		if ( ! $input || ! $type || ! $field_id ) {
			return $input;
		}

		/**
		 * Filter to modify a setting field value before validation.
		 *
		 * This cannot be used to filter the returned value of a custom
		 * setting type. You must use the `ot_validate_setting_input_safe`
		 * filter to ensure custom setting types are saved to the database.
		 *
		 * @param mixed  $input    The setting field value.
		 * @param string $type     The setting field type.
		 * @param string $field_id The setting field ID.
		 */
		$input = apply_filters( 'ot_validate_setting', $input, $type, $field_id );

		/**
		 * Filter to validate a setting field value.
		 *
		 * @param mixed  $input_safe This is either null, or the filtered input value.
		 * @param mixed  $input      The setting field value.
		 * @param string $type       The setting field type.
		 * @param string $field_id   The setting field ID.
		 */
		$input_safe = apply_filters( 'ot_validate_setting_input_safe', null, $input, $type, $field_id );

		// The value was filtered and is safe to return.
		if ( ! is_null( $input_safe ) ) {
			return $input_safe;
		}

		/* translators: %1$s: the input id, %2$s: the field id */
		$string_nums = esc_html__( 'The %1$s input field for %2$s only allows numeric values.', 'hueman' );

		if ( 'background' === $type ) {

			$input_safe = array();

			// Loop over array and check for values.
			foreach ( (array) $input as $key => $value ) {
				if ( 'background-color' === $key ) {
					$input_safe[ $key ] = ot_validate_setting( $value, 'colorpicker', $field_id );
				} elseif ( 'background-image' === $key ) {
					$input_safe[ $key ] = ot_validate_setting( $value, 'upload', $field_id );
				} else {
					$input_safe[ $key ] = sanitize_text_field( $value );
				}
			}
		} elseif ( 'border' === $type ) {

			$input_safe = array();

			// Loop over array and set errors or unset key from array.
			foreach ( $input as $key => $value ) {

				if ( empty( $value ) ) {
					continue;
				}

				// Validate width.
				if ( 'width' === $key ) {
					if ( ! is_numeric( $value ) ) {
						add_settings_error( 'option-tree', 'invalid_border_width', sprintf( $string_nums, '<code>width</code>', '<code>' . $field_id . '</code>' ), 'error' );
					} else {
						$input_safe[ $key ] = absint( $value );
					}
				} elseif ( 'color' === $key ) {
					$input_safe[ $key ] = ot_validate_setting( $value, 'colorpicker', $field_id );
				} else {
					$input_safe[ $key ] = sanitize_text_field( $value );
				}
			}
		} elseif ( 'box-shadow' === $type ) {

			$input_safe = array();

			// Loop over array and check for values.
			foreach ( (array) $input as $key => $value ) {
				if ( 'inset' === $key ) {
					$input_safe[ $key ] = 'inset';
				} elseif ( 'color' === $key ) {
					$input_safe[ $key ] = ot_validate_setting( $value, 'colorpicker', $field_id );
				} else {
					$input_safe[ $key ] = sanitize_text_field( $value );
				}
			}
		} elseif ( 'checkbox' === $type ) {

			$input_safe = array();

			// Loop over array and check for values.
			foreach ( (array) $input as $key => $value ) {
				if ( ! empty( $value ) ) {
					$input_safe[ $key ] = sanitize_text_field( $value );
				}
			}
		} elseif ( 'colorpicker' === $type ) {

			$input_safe = '';

			// Only strings are allowed.
			if ( is_string( $input ) ) {

				/* translators: %s: the field id */
				$string_color = esc_html__( 'The %s Colorpicker only allows valid hexadecimal or rgba values depending on the setting type.', 'hueman' );

				if ( 0 === preg_match( '/^#([a-f0-9]{6}|[a-f0-9]{3})$/i', $input ) && 0 === preg_match( '/^rgba\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9\.]{1,4})\s*\)/i', $input ) ) {
					add_settings_error( 'option-tree', 'invalid_hex_or_rgba', sprintf( $string_color, '<code>' . $field_id . '</code>' ), 'error' );
				} else {
					$input_safe = $input;
				}
			}
		} elseif ( 'colorpicker-opacity' === $type ) {
			$input_safe = ot_validate_setting( $input, 'colorpicker', $field_id );
		} elseif ( in_array( $type, array( 'category-checkbox', 'custom-post-type-checkbox', 'page-checkbox', 'post-checkbox', 'tag-checkbox', 'taxonomy-checkbox' ), true ) ) {

			$input_safe = array();

			// Loop over array and check for values.
			foreach ( (array) $input as $key => $value ) {
				if ( filter_var( $value, FILTER_VALIDATE_INT ) && 0 < $value ) {
					$input_safe[ $key ] = absint( $value );
				}
			}
		} elseif ( in_array( $type, array( 'category-select', 'custom-post-type-select', 'page-select', 'post-select', 'tag-select', 'taxonomy-select' ), true ) ) {

			$input_safe = '';

			if ( filter_var( $input, FILTER_VALIDATE_INT ) && 0 < $input ) {
				$input_safe = absint( $input );
			}
		} elseif ( in_array( $type, array( 'css', 'javascript', 'text', 'textarea', 'textarea-simple' ), true ) ) {
			if ( ! function_exists( '_filter_wp_kses_post' ) ) {
				/**
				 * Filter the allowed HTML and safe CSS styles.
				 *
				 * @since 2.7.2
				 *
				 * @param bool $add Whether to add or remove the filter.
				 */
				function _filter_wp_kses_post( $add = true ) {
					$css_filter = function ( $attr ) {
						array_push( $attr, 'display', 'visibility' );

						$attr = apply_filters( 'ot_safe_style_css', $attr );

						return $attr;
					};

					$html_filter = function ( $tags, $context ) {
						if ( 'post' === $context ) {
							if ( current_user_can( 'unfiltered_html' ) || true === OT_ALLOW_UNFILTERED_HTML ) {
								$tags['script']   = array_fill_keys( array( 'async', 'charset', 'defer', 'src', 'type' ), true );
								$tags['style']    = array_fill_keys( array( 'media', 'type' ), true );
								$tags['iframe']   = array_fill_keys( array( 'align', 'allowfullscreen', 'class', 'frameborder', 'height', 'id', 'longdesc', 'marginheight', 'marginwidth', 'name', 'sandbox', 'scrolling', 'src', 'srcdoc', 'style', 'width' ), true );
								$tags['noscript'] = true;

								$tags = apply_filters( 'ot_allowed_html', $tags );
							}
						}

						return $tags;
					};

					if ( $add ) {
						add_filter( 'safe_style_css', $css_filter );
						add_filter( 'wp_kses_allowed_html', $html_filter, 10, 2 );
					} else {
						remove_filter( 'safe_style_css', $css_filter );
						remove_filter( 'wp_kses_allowed_html', $html_filter );
					}
				}
			}

			_filter_wp_kses_post( true );
			$input_safe = wp_kses_post( $input );
			_filter_wp_kses_post( false );
		} elseif ( 'date-picker' === $type || 'date-time-picker' === $type ) {
			if ( ! empty( $input ) && (bool) strtotime( $input ) ) {
				$input_safe = sanitize_text_field( $input );
			}
		} elseif ( 'dimension' === $type ) {

			$input_safe = array();

			// Loop over array and set errors.
			foreach ( $input as $key => $value ) {
				if ( ! empty( $value ) ) {
					if ( ! is_numeric( $value ) && 'unit' !== $key ) {
						add_settings_error( 'option-tree', 'invalid_dimension_' . $key, sprintf( $string_nums, '<code>' . $key . '</code>', '<code>' . $field_id . '</code>' ), 'error' );
					} else {
						$input_safe[ $key ] = sanitize_text_field( $value );
					}
				}
			}
		} elseif ( 'gallery' === $type ) {

			$input_safe = '';

			if ( '' !== trim( $input ) ) {
				$input_safe = sanitize_text_field( $input );
			}
		} elseif ( 'google-fonts' === $type ) {

			$input_safe = array();

			// Loop over array.
			foreach ( $input as $key => $value ) {
				if ( '%key%' === $key ) {
					continue;
				}

				foreach ( $value as $fk => $fvalue ) {
					if ( is_array( $fvalue ) ) {
						foreach ( $fvalue as $sk => $svalue ) {
							$input_safe[ $key ][ $fk ][ $sk ] = sanitize_text_field( $svalue );
						}
					} else {
						$input_safe[ $key ][ $fk ] = sanitize_text_field( $fvalue );
					}
				}
			}

			array_values( $input_safe );
		} elseif ( 'link-color' === $type ) {

			$input_safe = array();

			// Loop over array and check for values.
			if ( is_array( $input ) && ! empty( $input ) ) {
				foreach ( $input as $key => $value ) {
					if ( ! empty( $value ) ) {
						$input_safe[ $key ] = ot_validate_setting( $input[ $key ], 'colorpicker', $field_id . '-' . $key );
					}
				}
			}

			array_filter( $input_safe );
		} elseif ( 'measurement' === $type ) {

			$input_safe = array();

			foreach ( $input as $key => $value ) {
				if ( ! empty( $value ) ) {
					$input_safe[ $key ] = sanitize_text_field( $value );
				}
			}
		} elseif ( 'numeric-slider' === $type ) {
			$input_safe = '';

			if ( ! empty( $input ) ) {
				if ( ! is_numeric( $input ) ) {
					add_settings_error( 'option-tree', 'invalid_numeric_slider', sprintf( $string_nums, '<code>' . esc_html__( 'slider', 'hueman' ) . '</code>', '<code>' . $field_id . '</code>' ), 'error' );
				} else {
					$input_safe = sanitize_text_field( $input );
				}
			}
		} elseif ( 'on-off' === $type ) {
			$input_safe = '';

			if ( ! empty( $input ) ) {
				$input_safe = sanitize_text_field( $input );
			}
		} elseif ( 'radio' === $type || 'radio-image' === $type || 'select' === $type || 'sidebar-select' === $type ) {
			$input_safe = '';

			if ( ! empty( $input ) ) {
				$input_safe = sanitize_text_field( $input );
			}
		} elseif ( 'spacing' === $type ) {

			$input_safe = array();

			// Loop over array and set errors.
			foreach ( $input as $key => $value ) {
				if ( ! empty( $value ) ) {
					if ( ! is_numeric( $value ) && 'unit' !== $key ) {
						add_settings_error( 'option-tree', 'invalid_spacing_' . $key, sprintf( $string_nums, '<code>' . $key . '</code>', '<code>' . $field_id . '</code>' ), 'error' );
					} else {
						$input_safe[ $key ] = sanitize_text_field( $value );
					}
				}
			}
		} elseif ( 'typography' === $type && isset( $input['font-color'] ) ) {

			$input_safe = array();

			// Loop over array and check for values.
			foreach ( $input as $key => $value ) {
				if ( 'font-color' === $key ) {
					$input_safe[ $key ] = ot_validate_setting( $value, 'colorpicker', $field_id );
				} else {
					$input_safe[ $key ] = sanitize_text_field( $value );
				}
			}
		} elseif ( 'upload' === $type ) {

			$input_safe = filter_var( $input, FILTER_VALIDATE_INT );

			if ( false === $input_safe && is_string( $input ) ) {
				$input_safe = esc_url_raw( $input );
			}
		} elseif ( 'url' === $type ) {

			$input_safe = '';

			if ( ! empty( $input ) ) {
				$input_safe = esc_url_raw( $input );
			}
		} else {

			/* translators: %1$s: the calling function, %2$s the filter name, %3$s the option type, %4$s the version number */
			$string_error = esc_html__( 'Notice: %1$s was called incorrectly. All stored data must be filtered through %2$s, the %3$s option type is not using this filter. This is required since version %4$s.', 'hueman' );

			// Log a user notice that things have changed since the last version.
			add_settings_error( 'option-tree', 'ot_validate_setting_error', sprintf( $string_error, '<code>ot_validate_setting</code>', '<code>ot_validate_setting_input_safe</code>', '<code>' . $type . '</code>', '<code>2.7.0</code>' ), 'error' );

			$input_safe = '';

			/*
			 * We don't know what the setting type is, so fallback to `sanitize_textarea_field`
			 * on all values and do a best-effort sanitize of the user data before saving it.
			 */
			if ( ! is_object( $input ) ) {

				// Contains an integer, float, string or boolean.
				if ( is_scalar( $input ) ) {
					$input_safe = sanitize_textarea_field( $input );
				} else {
					if ( ! function_exists( '_sanitize_recursive' ) ) {
						/**
						 * Filter the array values recursively.
						 *
						 * @param array $values The value to sanitize.
						 *
						 * @return array
						 */
						function _sanitize_recursive( $values = array() ) {
							$result = array();
							foreach ( $values as $key => $value ) {
								if ( ! is_object( $value ) ) {
									if ( is_scalar( $value ) ) {
										$result[ $key ] = sanitize_textarea_field( $value );
									} else {
										$result[ $key ] = _sanitize_recursive( $value );
									}
								}
							}

							return $result;
						}
					}
					$input_safe = _sanitize_recursive( $input );
				}
			}
		}

		// WPML Register and Unregister strings.
		if ( ! empty( $wmpl_id ) ) {

			// Allow filtering on the WPML option types.
			$single_string_types = apply_filters( 'ot_wpml_option_types', array( 'text', 'textarea', 'textarea-simple' ) );

			if ( in_array( $type, $single_string_types, true ) ) {
				if ( ! empty( $input_safe ) ) {
					ot_wpml_register_string( $wmpl_id, $input_safe );
				} else {
					ot_wpml_unregister_string( $wmpl_id );
				}
			}
		}

		/**
		 * Filter to modify the validated setting field value.
		 *
		 * It's important to note that the filter does not have access to
		 * the original value and can only modify the validated input value.
		 * This is a breaking change as of version 2.7.0.
		 *
		 * @param mixed  $input_safe The setting field value.
		 * @param string $type       The setting field type.
		 * @param string $field_id   The setting field ID.
		 */
		$input_safe = apply_filters( 'ot_after_validate_setting', $input_safe, $type, $field_id );

		return $input_safe;
	}
}

if ( ! function_exists( 'ot_admin_styles' ) ) {

	/**
	 * Setup the default admin styles
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_admin_styles() {
		global $wp_styles, $post;

		// Execute styles before actions.
		do_action( 'ot_admin_styles_before' );

		// Load WP colorpicker.
		wp_enqueue_style( 'wp-color-picker' );

		// @hu_custom: version as time().
		// Load admin styles.
		wp_enqueue_style( 'ot-admin-css', OT_URL . 'assets/css/ot-admin.css', false, time() );

		// Load the RTL stylesheet.
		$wp_styles->add_data( 'ot-admin-css', 'rtl', true );

		// Remove styles added by the Easy Digital Downloads plugin.
		if ( isset( $post->post_type ) && 'post' === $post->post_type ) {
			wp_dequeue_style( 'jquery-ui-css' );
		}

		/**
		 * Filter the screen IDs used to dequeue `jquery-ui-css`.
		 *
		 * @since 2.5.0
		 *
		 * @param array $screen_ids An array of screen IDs.
		 */
		$screen_ids = apply_filters(
			'ot_dequeue_jquery_ui_css_screen_ids',
			array(
				'toplevel_page_ot-settings',
				'optiontree_page_ot-documentation',
				'appearance_page_ot-theme-options',
			)
		);

		// Remove styles added by the WP Review plugin and any custom pages added through filtering.
		if ( in_array( get_current_screen()->id, $screen_ids, true ) ) {
			wp_dequeue_style( 'plugin_name-admin-ui-css' );
			wp_dequeue_style( 'jquery-ui-css' );
		}

		// Execute styles after actions.
		do_action( 'ot_admin_styles_after' );
	}
}

if ( ! function_exists( 'ot_admin_scripts' ) ) {

	/**
	 * Setup the default admin scripts.
	 *
	 * @uses add_thickbox() Include Thickbox for file uploads.
	 * @uses wp_enqueue_script() Add OptionTree scripts.
	 * @uses wp_localize_script() Used to include arbitrary Javascript data.
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_admin_scripts() {

		// Execute scripts before actions.
		do_action( 'ot_admin_scripts_before' );

		if ( function_exists( 'wp_enqueue_media' ) ) {
			// WP 3.5 Media Uploader.
			wp_enqueue_media();
		} else {
			// Legacy Thickbox.
			add_thickbox();
		}

		// Load jQuery-ui slider.
		wp_enqueue_script( 'jquery-ui-slider' );

		/**
	 	 * @hu_custom
		 * Hueman doesn't need date and time picker jquery plugins
		 * we remove them for compatibility reasons.
		 * See : https://github.com/presscustomizr/hueman/issues/454
		 */
		// Load jQuery-ui datepicker.
		// wp_enqueue_script( 'jquery-ui-datepicker' );

		// Load WP colorpicker.
		wp_enqueue_script( 'wp-color-picker' );

		// Load jQuery UI timepicker addon.
		// wp_enqueue_script( 'jquery-ui-timepicker', OT_URL . 'assets/js/vendor/jquery/jquery-ui-timepicker.js', array( 'jquery', 'jquery-ui-slider', 'jquery-ui-datepicker' ), '1.4.3', false );

		// Load the post formats.
		if ( true === OT_META_BOXES && true === OT_POST_FORMATS ) {
			wp_enqueue_script( 'ot-postformats', OT_URL . 'assets/js/ot-postformats.js', array( 'jquery' ), '1.0.1', false );
		}

		/**
	 	 * @hu_custom
		 * Hueman doesn't need date and time picker jquery plugins
		 * we remove them for compatibility reasons.
		 * See : https://github.com/presscustomizr/hueman/issues/454
		 */
		// Load all the required scripts.
		wp_enqueue_script( 'ot-admin-js', OT_URL . 'assets/js/ot-admin.js', array(
			'jquery',
			'jquery-ui-tabs',
			// 'jquery-ui-sortable',
			// 'jquery-ui-slider',
			'wp-color-picker',
			// 'jquery-ui-datepicker',
			// 'jquery-ui-timepicker'
		), OT_VERSION, false );

		// Create localized JS array.
		$localized_array = array(
			'ajax'                  => admin_url( 'admin-ajax.php' ),
			'nonce'                 => wp_create_nonce( 'option_tree' ),
			'upload_text'           => apply_filters( 'ot_upload_text', __( 'Send to OptionTree', 'hueman' ) ),
			'remove_media_text'     => esc_html__( 'Remove Media', 'hueman' ),
			'reset_agree'           => esc_html__( 'Are you sure you want to reset back to the defaults?', 'hueman' ),
			'remove_no'             => esc_html__( 'You can\'t remove this! But you can edit the values.', 'hueman' ),
			'remove_agree'          => esc_html__( 'Are you sure you want to remove this?', 'hueman' ),
			'activate_layout_agree' => esc_html__( 'Are you sure you want to activate this layout?', 'hueman' ),
			'setting_limit'         => esc_html__( 'Sorry, you can\'t have settings three levels deep.', 'hueman' ),
			'delete'                => esc_html__( 'Delete Gallery', 'hueman' ),
			'edit'                  => esc_html__( 'Edit Gallery', 'hueman' ),
			'create'                => esc_html__( 'Create Gallery', 'hueman' ),
			'confirm'               => esc_html__( 'Are you sure you want to delete this Gallery?', 'hueman' ),
			'date_current'          => esc_html__( 'Today', 'hueman' ),
			'date_time_current'     => esc_html__( 'Now', 'hueman' ),
			'date_close'            => esc_html__( 'Close', 'hueman' ),
			'replace'               => esc_html__( 'Featured Image', 'hueman' ),
			'with'                  => esc_html__( 'Image', 'hueman' ),
		);

		// Localized script attached to 'option_tree'.
		wp_localize_script( 'ot-admin-js', 'option_tree', $localized_array );

		// Execute scripts after actions.
		do_action( 'ot_admin_scripts_after' );
	}
}


if ( ! function_exists( 'ot_create_media_post' ) ) {

	/**
	 * Register custom post type & create the media post used to attach images.
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_create_media_post() {}
}




if ( ! function_exists( 'ot_save_css' ) ) {

	/**
	 * Helper function to update the CSS option type after save.
	 *
	 * This function is called during the `ot_after_theme_options_save` hook,
	 * which is passed the currently stored options array.
	 *
	 * @param array $options The current stored options array.
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_save_css( $options ) {

		// Grab a copy of the settings.
		$settings = get_option( ot_settings_id() );

		// Has settings.
		if ( isset( $settings['settings'] ) ) {

			// Loop through sections and insert CSS when needed.
			foreach ( $settings['settings'] as $k => $setting ) {

				// Is the CSS option type.
				if ( isset( $setting['type'] ) && 'css' === $setting['type'] ) {

					// Insert CSS into dynamic.css.
					if ( isset( $options[ $setting['id'] ] ) && '' !== $options[ $setting['id'] ] ) {
						ot_insert_css_with_markers( $setting['id'], $options[ $setting['id'] ] );

						// Remove old CSS from dynamic.css.
					} else {
						ot_remove_old_css( $setting['id'] );
					}
				}
			}
		}
	}
}



if ( ! function_exists( 'ot_save_settings' ) ) {

	/**
	 * Save settings array before the screen is displayed.
	 *
	 * @return bool Redirects on save, false on failure.
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_save_settings() {

		// Check and verify import settings nonce.
		if ( isset( $_POST['option_tree_settings_nonce'] ) && wp_verify_nonce( $_POST['option_tree_settings_nonce'], 'option_tree_settings_form' ) ) { // phpcs:ignore

			// Settings value.
			$settings = isset( $_POST[ ot_settings_id() ] ) ? wp_unslash( $_POST[ ot_settings_id() ] ) : array(); // phpcs:ignore

			$settings_safe = ot_validate_settings( $settings );

			// Default message.
			$message = 'failed';

			// Save & show success message.
			if ( ! empty( $settings_safe ) ) {
				ot_wpml_unregister( $settings_safe );

				update_option( ot_settings_id(), $settings_safe );
				$message = 'success';
			}

			// Redirect.
			wp_safe_redirect(
				esc_url_raw(
					add_query_arg(
						array(
							'action'  => 'save-settings',
							'message' => $message,
						),
						wp_get_referer()
					)
				)
			);
			exit;
		}

		return false;
	}
}

if ( ! function_exists( 'ot_wpml_unregister' ) ) {

	/**
	 * Unregister WPML strings based on settings changing.
	 *
	 * @param array $settings The array of settings.
	 *
	 * @access public
	 * @since  2.7.0
	 */
	function ot_wpml_unregister( $settings = array() ) {

		// WPML unregister ID's that have been removed.
		if ( function_exists( 'icl_unregister_string' ) ) {

			$current = get_option( ot_settings_id() );
			$options = get_option( ot_options_id() );

			if ( isset( $current['settings'] ) ) {

				// Empty ID array.
				$new_ids = array();

				// Build the WPML IDs array.
				foreach ( $settings['settings'] as $setting ) {
					if ( $setting['id'] ) {
						$new_ids[] = $setting['id'];
					}
				}

				// Remove missing IDs from WPML.
				foreach ( $current['settings'] as $current_setting ) {
					if ( ! in_array( $current_setting['id'], $new_ids, true ) ) {
						if ( ! empty( $options[ $current_setting['id'] ] ) && in_array( $current_setting['type'], array( 'list-item', 'slider' ), true ) ) {
							foreach ( $options[ $current_setting['id'] ] as $key => $value ) {
								foreach ( $value as $ckey => $cvalue ) {
									ot_wpml_unregister_string( $current_setting['id'] . '_' . $ckey . '_' . $key );
								}
							}
						} elseif ( ! empty( $options[ $current_setting['id'] ] ) && 'social-icons' === $current_setting['type'] ) {
							foreach ( $options[ $current_setting['id'] ] as $key => $value ) {
								foreach ( $value as $ckey => $cvalue ) {
									ot_wpml_unregister_string( $current_setting['id'] . '_' . $ckey . '_' . $key );
								}
							}
						} else {
							ot_wpml_unregister_string( $current_setting['id'] );
						}
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'ot_validate_settings' ) ) {

	/**
	 * Helper function to validate all settings.
	 *
	 * This includes the `sections`, `settings`, and `contextual_help` arrays.
	 *
	 * @param array $settings The array of settings.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.7.0
	 */
	function ot_validate_settings( $settings = array() ) {

		// Store the validated settings.
		$settings_safe = array();

		// Validate sections.
		if ( isset( $settings['sections'] ) ) {

			// Fix numeric keys since drag & drop will change them.
			$settings['sections'] = array_values( $settings['sections'] );

			// Loop through sections.
			foreach ( $settings['sections'] as $k => $section ) {

				// Skip if missing values.
				if ( ( ! isset( $section['title'] ) && ! isset( $section['id'] ) ) || ( '' === $section['title'] && '' === $section['id'] ) ) {
					continue;
				}

				// Validate label.
				if ( '' !== $section['title'] ) {
					$settings_safe['sections'][ $k ]['title'] = wp_kses_post( $section['title'] );
				}

				// Missing title set to unfiltered ID.
				if ( ! isset( $section['title'] ) || '' === $section['title'] ) {

					$settings_safe['sections'][ $k ]['title'] = wp_kses_post( $section['id'] );

					// Missing ID set to title.
				} elseif ( ! isset( $section['id'] ) || '' === $section['id'] ) {

					$settings_safe['id'] = wp_kses_post( $section['title'] );
				}

				// Sanitize ID once everything has been checked first.
				$settings_safe['sections'][ $k ]['id'] = ot_sanitize_option_id( wp_kses_post( $section['id'] ) );
			}
		}

		// Validate settings by looping over array as many times as it takes.
		if ( isset( $settings['settings'] ) ) {
			$settings_safe['settings'] = ot_validate_settings_array( $settings['settings'] );
		}

		// Validate contextual_help.
		if ( isset( $settings['contextual_help']['content'] ) ) {

			// Fix numeric keys since drag & drop will change them.
			$settings['contextual_help']['content'] = array_values( $settings['contextual_help']['content'] );

			// Loop through content.
			foreach ( $settings['contextual_help']['content'] as $k => $content ) {

				// Skip if missing values.
				if ( ( ! isset( $content['title'] ) && ! isset( $content['id'] ) ) || ( '' === $content['title'] && '' === $content['id'] ) ) {
					continue;
				}

				// Validate label.
				if ( '' !== $content['title'] ) {
					$settings_safe['contextual_help']['content'][ $k ]['title'] = wp_kses_post( $content['title'] );
				}

				// Missing title set to unfiltered ID.
				if ( ! isset( $content['title'] ) || '' === $content['title'] ) {

					$settings_safe['contextual_help']['content'][ $k ]['title'] = wp_kses_post( $content['id'] );

					// Missing ID set to title.
				} elseif ( ! isset( $content['id'] ) || '' === $content['id'] ) {

					$content['id'] = wp_kses_post( $content['title'] );
				}

				// Sanitize ID once everything has been checked first.
				$settings_safe['contextual_help']['content'][ $k ]['id'] = ot_sanitize_option_id( wp_kses_post( $content['id'] ) );

				// Validate textarea description.
				if ( isset( $content['content'] ) ) {
					$settings_safe['contextual_help']['content'][ $k ]['content'] = wp_kses_post( $content['content'] );
				}
			}
		}

		// Validate contextual_help sidebar.
		if ( isset( $settings['contextual_help']['sidebar'] ) ) {
			$settings_safe['contextual_help']['sidebar'] = wp_kses_post( $settings['contextual_help']['sidebar'] );
		}

		return $settings_safe;
	}
}

if ( ! function_exists( 'ot_validate_settings_array' ) ) {

	/**
	 * Validate a settings array before save.
	 *
	 * This function will loop over a settings array as many
	 * times as it takes to validate every sub setting.
	 *
	 * @param  array $settings The array of settings.
	 * @return array
	 *
	 * @access  public
	 * @since   2.0
	 * @updated 2.7.0
	 */
	function ot_validate_settings_array( $settings = array() ) {

		// Field types mapped to their sanitize function.
		$field_types = array(
			'label'        => 'wp_kses_post',
			'id'           => 'ot_sanitize_option_id',
			'type'         => 'sanitize_text_field',
			'desc'         => 'wp_kses_post',
			'settings'     => 'ot_validate_settings_array',
			'choices'      => array(
				'label' => 'wp_kses_post',
				'value' => 'sanitize_text_field',
				'src'   => 'sanitize_text_field',
			),
			'std'          => 'sanitize_text_field',
			'rows'         => 'absint',
			'post_type'    => 'sanitize_text_field',
			'taxonomy'     => 'sanitize_text_field',
			'min_max_step' => 'sanitize_text_field',
			'class'        => 'sanitize_text_field',
			'condition'    => 'sanitize_text_field',
			'operator'     => 'sanitize_text_field',
			'section'      => 'sanitize_text_field',
		);

		// Store the validated settings.
		$settings_safe = array();

		// Validate settings.
		if ( 0 < count( $settings ) ) {

			// Fix numeric keys since drag & drop will change them.
			$settings = array_values( $settings );

			// Loop through settings.
			foreach ( $settings as $sk => $setting ) {
				foreach ( $setting as $fk => $field ) {
					if ( isset( $field_types[ $fk ] ) ) {
						if ( 'choices' === $fk ) {
							foreach ( $field as $ck => $choice ) {
								foreach ( $choice as $vk => $value ) {
									$settings_safe[ $sk ][ $fk ][ $ck ][ $vk ] = call_user_func( $field_types[ $fk ][ $vk ], $value );
								}
							}
						} elseif ( 'std' === $fk && is_array( $field ) ) {
							$callback  = $field_types[ $fk ];
							$array_map = function( $item ) use ( $array_map, $callback ) {
								return is_array( $item ) ? array_map( $array_map, $item ) : call_user_func( $callback, $item );
							};

							$settings_safe[ $sk ][ $fk ] = array_map( $array_map, $field );
						} else {
							$sanitized = call_user_func( $field_types[ $fk ], $field );
							if ( 'rows' === $fk && 0 === $sanitized ) {
								$sanitized = '';
							}
							$settings_safe[ $sk ][ $fk ] = $sanitized;
						}
					}
				}
			}
		}

		return $settings_safe;
	}
}


if ( ! function_exists( 'ot_alert_message' ) ) {

	/**
	 * Helper function to display alert messages.
	 *
	 * @param  array $page Page array.
	 * @return mixed
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_alert_message( $page = array() ) {

		if ( empty( $page ) ) {
			return false;
		}

		$before = apply_filters( 'ot_before_page_messages', '', $page );

		if ( $before ) {
			return $before;
		}

		$action  = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : ''; // phpcs:ignore
		$updated = isset( $_REQUEST['settings-updated'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['settings-updated'] ) ) : ''; // phpcs:ignore

		if ( 'save-settings' === $action ) {

			if ( 'success' === $message ) {

				return '<div id="message" class="updated fade below-h2"><p>' . esc_html__( 'Settings updated.', 'hueman' ) . '</p></div>';

			} elseif ( 'failed' === $message ) {

				return '<div id="message" class="error fade below-h2"><p>' . esc_html__( 'Settings could not be saved.', 'hueman' ) . '</p></div>';

			}
		} elseif ( 'import-xml' === $action || 'import-settings' === $action ) {

			if ( 'success' === $message ) {

				return '<div id="message" class="updated fade below-h2"><p>' . esc_html__( 'Settings Imported.', 'hueman' ) . '</p></div>';

			} elseif ( 'failed' === $message ) {

				return '<div id="message" class="error fade below-h2"><p>' . esc_html__( 'Settings could not be imported.', 'hueman' ) . '</p></div>';

			}
		} elseif ( 'import-data' === $action ) {

			if ( 'success' === $message ) {

				return '<div id="message" class="updated fade below-h2"><p>' . esc_html__( 'Data Imported.', 'hueman' ) . '</p></div>';

			} elseif ( 'failed' === $message ) {

				return '<div id="message" class="error fade below-h2"><p>' . esc_html__( 'Data could not be imported.', 'hueman' ) . '</p></div>';

			}
		} elseif ( 'import-layouts' === $action ) {

			if ( 'success' === $message ) {

				return '<div id="message" class="updated fade below-h2"><p>' . esc_html__( 'Layouts Imported.', 'hueman' ) . '</p></div>';

			} elseif ( 'failed' === $message ) {

				return '<div id="message" class="error fade below-h2"><p>' . esc_html__( 'Layouts could not be imported.', 'hueman' ) . '</p></div>';

			}
		} elseif ( 'save-layouts' === $action ) {

			if ( 'success' === $message ) {

				return '<div id="message" class="updated fade below-h2"><p>' . esc_html__( 'Layouts Updated.', 'hueman' ) . '</p></div>';

			} elseif ( 'failed' === $message ) {

				return '<div id="message" class="error fade below-h2"><p>' . esc_html__( 'Layouts could not be updated.', 'hueman' ) . '</p></div>';

			} elseif ( 'deleted' === $message ) {

				return '<div id="message" class="updated fade below-h2"><p>' . esc_html__( 'Layouts have been deleted.', 'hueman' ) . '</p></div>';

			}
		} elseif ( 'layout' === $updated ) {

			return '<div id="message" class="updated fade below-h2"><p>' . esc_html__( 'Layout activated.', 'hueman' ) . '</p></div>';

		} elseif ( 'reset' === $action ) {

			return '<div id="message" class="updated fade below-h2"><p>' . $page['reset_message'] . '</p></div>';

		}

		do_action( 'ot_custom_page_messages', $page );

		if ( 'true' === $updated || true === $updated ) {
			return '<div id="message" class="updated fade below-h2"><p>' . $page['updated_message'] . '</p></div>';
		}

		return false;
	}
}

if ( ! function_exists( 'ot_option_types_array' ) ) {

	/**
	 * Setup the default option types.
	 *
	 * The returned option types are filterable so you can add your own.
	 * This is not a task for a beginner as you'll need to add the function
	 * that displays the option to the user and validate the saved data.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_option_types_array() {

		return apply_filters(
			'ot_option_types_array',
			array(
				'background'                => esc_html__( 'Background', 'hueman' ),
				'border'                    => esc_html__( 'Border', 'hueman' ),
				'box-shadow'                => esc_html__( 'Box Shadow', 'hueman' ),
				'category-checkbox'         => esc_html__( 'Category Checkbox', 'hueman' ),
				'category-select'           => esc_html__( 'Category Select', 'hueman' ),
				'checkbox'                  => esc_html__( 'Checkbox', 'hueman' ),
				'colorpicker'               => esc_html__( 'Colorpicker', 'hueman' ),
				'colorpicker-opacity'       => esc_html__( 'Colorpicker Opacity', 'hueman' ),
				'css'                       => esc_html__( 'CSS', 'hueman' ),
				'custom-post-type-checkbox' => esc_html__( 'Custom Post Type Checkbox', 'hueman' ),
				'custom-post-type-select'   => esc_html__( 'Custom Post Type Select', 'hueman' ),
				'date-picker'               => esc_html__( 'Date Picker', 'hueman' ),
				'date-time-picker'          => esc_html__( 'Date Time Picker', 'hueman' ),
				'dimension'                 => esc_html__( 'Dimension', 'hueman' ),
				'gallery'                   => esc_html__( 'Gallery', 'hueman' ),
				'google-fonts'              => esc_html__( 'Google Fonts', 'hueman' ),
				'javascript'                => esc_html__( 'JavaScript', 'hueman' ),
				'link-color'                => esc_html__( 'Link Color', 'hueman' ),
				'list-item'                 => esc_html__( 'List Item', 'hueman' ),
				'measurement'               => esc_html__( 'Measurement', 'hueman' ),
				'numeric-slider'            => esc_html__( 'Numeric Slider', 'hueman' ),
				'on-off'                    => esc_html__( 'On/Off', 'hueman' ),
				'page-checkbox'             => esc_html__( 'Page Checkbox', 'hueman' ),
				'page-select'               => esc_html__( 'Page Select', 'hueman' ),
				'post-checkbox'             => esc_html__( 'Post Checkbox', 'hueman' ),
				'post-select'               => esc_html__( 'Post Select', 'hueman' ),
				'radio'                     => esc_html__( 'Radio', 'hueman' ),
				'radio-image'               => esc_html__( 'Radio Image', 'hueman' ),
				'select'                    => esc_html__( 'Select', 'hueman' ),
				'sidebar-select'            => esc_html__( 'Sidebar Select', 'hueman' ),
				'slider'                    => esc_html__( 'Slider', 'hueman' ),
				'social-links'              => esc_html__( 'Social Links', 'hueman' ),
				'spacing'                   => esc_html__( 'Spacing', 'hueman' ),
				'tab'                       => esc_html__( 'Tab', 'hueman' ),
				'tag-checkbox'              => esc_html__( 'Tag Checkbox', 'hueman' ),
				'tag-select'                => esc_html__( 'Tag Select', 'hueman' ),
				'taxonomy-checkbox'         => esc_html__( 'Taxonomy Checkbox', 'hueman' ),
				'taxonomy-select'           => esc_html__( 'Taxonomy Select', 'hueman' ),
				'text'                      => esc_html__( 'Text', 'hueman' ),
				'textarea'                  => esc_html__( 'Textarea', 'hueman' ),
				'textarea-simple'           => esc_html__( 'Textarea Simple', 'hueman' ),
				'textblock'                 => esc_html__( 'Textblock', 'hueman' ),
				'textblock-titled'          => esc_html__( 'Textblock Titled', 'hueman' ),
				'typography'                => esc_html__( 'Typography', 'hueman' ),
				'upload'                    => esc_html__( 'Upload', 'hueman' ),
			)
		);
	}
}

if ( ! function_exists( 'ot_map_old_option_types' ) ) {

	/**
	 * Map old option types for rebuilding XML and Table data.
	 *
	 * @param  string $type The old option type.
	 * @return string The new option type
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_map_old_option_types( $type = '' ) {

		if ( empty( $type ) ) {
			return 'text';
		}

		$types = array(
			'background'   => 'background',
			'category'     => 'category-select',
			'categories'   => 'category-checkbox',
			'checkbox'     => 'checkbox',
			'colorpicker'  => 'colorpicker',
			'css'          => 'css',
			'custom_post'  => 'custom-post-type-select',
			'custom_posts' => 'custom-post-type-checkbox',
			'input'        => 'text',
			'image'        => 'upload',
			'measurement'  => 'measurement',
			'page'         => 'page-select',
			'pages'        => 'page-checkbox',
			'post'         => 'post-select',
			'posts'        => 'post-checkbox',
			'radio'        => 'radio',
			'select'       => 'select',
			'slider'       => 'slider',
			'tag'          => 'tag-select',
			'tags'         => 'tag-checkbox',
			'textarea'     => 'textarea',
			'textblock'    => 'textblock',
			'typography'   => 'typography',
			'upload'       => 'upload',
		);

		if ( isset( $types[ $type ] ) ) {
			return $types[ $type ];
		}

		return false;
	}
}

if ( ! function_exists( 'ot_google_font_stack' ) ) {

	/**
	 * Filters the typography font-family to add Google fonts dynamically.
	 *
	 * @param array  $families An array of all recognized font families.
	 * @param string $field_id ID of the field being filtered.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.5.0
	 */
	function ot_google_font_stack( $families, $field_id ) {

		if ( ! is_array( $families ) ) {
			return array();
		}

		$ot_google_fonts     = get_theme_mod( 'ot_google_fonts', array() );
		$ot_set_google_fonts = get_theme_mod( 'ot_set_google_fonts', array() );

		if ( ! empty( $ot_set_google_fonts ) ) {
			foreach ( $ot_set_google_fonts as $id => $sets ) {
				foreach ( $sets as $value ) {
					$family = isset( $value['family'] ) ? $value['family'] : '';
					if ( $family && isset( $ot_google_fonts[ $family ] ) ) {
						$spaces              = explode( ' ', $ot_google_fonts[ $family ]['family'] );
						$font_stack          = count( $spaces ) > 1 ? '"' . $ot_google_fonts[ $family ]['family'] . '"' : $ot_google_fonts[ $family ]['family'];
						$families[ $family ] = apply_filters( 'ot_google_font_stack', $font_stack, $family, $field_id );
					}
				}
			}
		}

		return $families;
	}

	add_filter( 'ot_recognized_font_families', 'ot_google_font_stack', 1, 2 );
}

if ( ! function_exists( 'ot_recognized_font_families' ) ) {

	/**
	 * Recognized font families
	 *
	 * Returns an array of all recognized font families.
	 * Keys are intended to be stored in the database
	 * while values are ready for display in html.
	 * Renamed in version 2.0 to avoid name collisions.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access  public
	 * @since   1.1.8
	 * @updated 2.0
	 */
	function ot_recognized_font_families( $field_id ) {

		$families = array(
			'arial'     => 'Arial',
			'georgia'   => 'Georgia',
			'helvetica' => 'Helvetica',
			'palatino'  => 'Palatino',
			'tahoma'    => 'Tahoma',
			'times'     => '"Times New Roman", sans-serif',
			'trebuchet' => 'Trebuchet',
			'verdana'   => 'Verdana',
		);

		return apply_filters( 'ot_recognized_font_families', $families, $field_id );
	}
}

if ( ! function_exists( 'ot_recognized_font_sizes' ) ) {

	/**
	 * Recognized font sizes
	 *
	 * Returns an array of all recognized font sizes.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.0.12
	 */
	function ot_recognized_font_sizes( $field_id ) {

		$range = ot_range(
			apply_filters( 'ot_font_size_low_range', 0, $field_id ),
			apply_filters( 'ot_font_size_high_range', 150, $field_id ),
			apply_filters( 'ot_font_size_range_interval', 1, $field_id )
		);

		$unit = apply_filters( 'ot_font_size_unit_type', 'px', $field_id );

		foreach ( $range as $k => $v ) {
			$range[ $k ] = $v . $unit;
		}

		return apply_filters( 'ot_recognized_font_sizes', $range, $field_id );
	}
}

if ( ! function_exists( 'ot_recognized_font_styles' ) ) {

	/**
	 * Recognized font styles
	 *
	 * Returns an array of all recognized font styles.
	 * Renamed in version 2.0 to avoid name collisions.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access  public
	 * @since   1.1.8
	 * @updated 2.0
	 */
	function ot_recognized_font_styles( $field_id ) {

		return apply_filters(
			'ot_recognized_font_styles',
			array(
				'normal'  => 'Normal',
				'italic'  => 'Italic',
				'oblique' => 'Oblique',
				'inherit' => 'Inherit',
			),
			$field_id
		);
	}
}

if ( ! function_exists( 'ot_recognized_font_variants' ) ) {

	/**
	 * Recognized font variants
	 *
	 * Returns an array of all recognized font variants.
	 * Renamed in version 2.0 to avoid name collisions.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access  public
	 * @since   1.1.8
	 * @updated 2.0
	 */
	function ot_recognized_font_variants( $field_id ) {

		return apply_filters(
			'ot_recognized_font_variants',
			array(
				'normal'     => 'Normal',
				'small-caps' => 'Small Caps',
				'inherit'    => 'Inherit',
			),
			$field_id
		);
	}
}

if ( ! function_exists( 'ot_recognized_font_weights' ) ) {

	/**
	 * Recognized font weights
	 *
	 * Returns an array of all recognized font weights.
	 * Renamed in version 2.0 to avoid name collisions.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access  public
	 * @since   1.1.8
	 * @updated 2.0
	 */
	function ot_recognized_font_weights( $field_id ) {

		return apply_filters(
			'ot_recognized_font_weights',
			array(
				'normal'  => 'Normal',
				'bold'    => 'Bold',
				'bolder'  => 'Bolder',
				'lighter' => 'Lighter',
				'100'     => '100',
				'200'     => '200',
				'300'     => '300',
				'400'     => '400',
				'500'     => '500',
				'600'     => '600',
				'700'     => '700',
				'800'     => '800',
				'900'     => '900',
				'inherit' => 'Inherit',
			),
			$field_id
		);
	}
}

if ( ! function_exists( 'ot_recognized_letter_spacing' ) ) {

	/**
	 * Recognized letter spacing
	 *
	 * Returns an array of all recognized line heights.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.0.12
	 */
	function ot_recognized_letter_spacing( $field_id ) {

		$range = ot_range(
			apply_filters( 'ot_letter_spacing_low_range', -0.1, $field_id ),
			apply_filters( 'ot_letter_spacing_high_range', 0.1, $field_id ),
			apply_filters( 'ot_letter_spacing_range_interval', 0.01, $field_id )
		);

		$unit = apply_filters( 'ot_letter_spacing_unit_type', 'em', $field_id );

		foreach ( $range as $k => $v ) {
			$range[ $k ] = $v . $unit;
		}

		return apply_filters( 'ot_recognized_letter_spacing', $range, $field_id );
	}
}

if ( ! function_exists( 'ot_recognized_line_heights' ) ) {

	/**
	 * Recognized line heights
	 *
	 * Returns an array of all recognized line heights.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.0.12
	 */
	function ot_recognized_line_heights( $field_id ) {

		$range = ot_range(
			apply_filters( 'ot_line_height_low_range', 0, $field_id ),
			apply_filters( 'ot_line_height_high_range', 150, $field_id ),
			apply_filters( 'ot_line_height_range_interval', 1, $field_id )
		);

		$unit = apply_filters( 'ot_line_height_unit_type', 'px', $field_id );

		foreach ( $range as $k => $v ) {
			$range[ $k ] = $v . $unit;
		}

		return apply_filters( 'ot_recognized_line_heights', $range, $field_id );
	}
}

if ( ! function_exists( 'ot_recognized_text_decorations' ) ) {

	/**
	 * Recognized text decorations
	 *
	 * Returns an array of all recognized text decorations.
	 * Keys are intended to be stored in the database
	 * while values are ready for display in html.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.0.10
	 */
	function ot_recognized_text_decorations( $field_id ) {

		return apply_filters(
			'ot_recognized_text_decorations',
			array(
				'blink'        => 'Blink',
				'inherit'      => 'Inherit',
				'line-through' => 'Line Through',
				'none'         => 'None',
				'overline'     => 'Overline',
				'underline'    => 'Underline',
			),
			$field_id
		);
	}
}

if ( ! function_exists( 'ot_recognized_text_transformations' ) ) {

	/**
	 * Recognized text transformations
	 *
	 * Returns an array of all recognized text transformations.
	 * Keys are intended to be stored in the database
	 * while values are ready for display in html.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.0.10
	 */
	function ot_recognized_text_transformations( $field_id ) {

		return apply_filters(
			'ot_recognized_text_transformations',
			array(
				'capitalize' => 'Capitalize',
				'inherit'    => 'Inherit',
				'lowercase'  => 'Lowercase',
				'none'       => 'None',
				'uppercase'  => 'Uppercase',
			),
			$field_id
		);
	}
}

if ( ! function_exists( 'ot_recognized_background_repeat' ) ) {

	/**
	 * Recognized background repeat
	 *
	 * Returns an array of all recognized background repeat values.
	 * Renamed in version 2.0 to avoid name collisions.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access  public
	 * @since   1.1.8
	 * @updated 2.0
	 */
	function ot_recognized_background_repeat( $field_id ) {

		return apply_filters(
			'ot_recognized_background_repeat',
			array(
				'no-repeat' => 'No Repeat',
				'repeat'    => 'Repeat All',
				'repeat-x'  => 'Repeat Horizontally',
				'repeat-y'  => 'Repeat Vertically',
				'inherit'   => 'Inherit',
			),
			$field_id
		);
	}
}

if ( ! function_exists( 'ot_recognized_background_attachment' ) ) {

	/**
	 * Recognized background attachment
	 *
	 * Returns an array of all recognized background attachment values.
	 * Renamed in version 2.0 to avoid name collisions.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access  public
	 * @since   1.1.8
	 * @updated 2.0
	 */
	function ot_recognized_background_attachment( $field_id ) {

		return apply_filters(
			'ot_recognized_background_attachment',
			array(
				'fixed'   => 'Fixed',
				'scroll'  => 'Scroll',
				'inherit' => 'Inherit',
			),
			$field_id
		);
	}
}

if ( ! function_exists( 'ot_recognized_background_position' ) ) {

	/**
	 * Recognized background position
	 *
	 * Returns an array of all recognized background position values.
	 * Renamed in version 2.0 to avoid name collisions.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access  public
	 * @since   1.1.8
	 * @updated 2.0
	 */
	function ot_recognized_background_position( $field_id ) {

		return apply_filters(
			'ot_recognized_background_position',
			array(
				'left top'      => 'Left Top',
				'left center'   => 'Left Center',
				'left bottom'   => 'Left Bottom',
				'center top'    => 'Center Top',
				'center center' => 'Center Center',
				'center bottom' => 'Center Bottom',
				'right top'     => 'Right Top',
				'right center'  => 'Right Center',
				'right bottom'  => 'Right Bottom',
			),
			$field_id
		);

	}
}

if ( ! function_exists( 'ot_recognized_border_style_types' ) ) {

	/**
	 * Returns an array of all available border style types.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.5.0
	 */
	function ot_recognized_border_style_types( $field_id ) {

		return apply_filters(
			'ot_recognized_border_style_types',
			array(
				'hidden' => 'Hidden',
				'dashed' => 'Dashed',
				'solid'  => 'Solid',
				'double' => 'Double',
				'groove' => 'Groove',
				'ridge'  => 'Ridge',
				'inset'  => 'Inset',
				'outset' => 'Outset',
			),
			$field_id
		);

	}
}

if ( ! function_exists( 'ot_recognized_border_unit_types' ) ) {

	/**
	 * Returns an array of all available border unit types.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.5.0
	 */
	function ot_recognized_border_unit_types( $field_id ) {

		return apply_filters(
			'ot_recognized_border_unit_types',
			array(
				'px' => 'px',
				'%'  => '%',
				'em' => 'em',
				'pt' => 'pt',
			),
			$field_id
		);
	}
}

if ( ! function_exists( 'ot_recognized_dimension_unit_types' ) ) {

	/**
	 * Returns an array of all available dimension unit types.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.5.0
	 */
	function ot_recognized_dimension_unit_types( $field_id = '' ) {

		return apply_filters(
			'ot_recognized_dimension_unit_types',
			array(
				'px' => 'px',
				'%'  => '%',
				'em' => 'em',
				'pt' => 'pt',
			),
			$field_id
		);
	}
}

if ( ! function_exists( 'ot_recognized_spacing_unit_types' ) ) {

	/**
	 * Returns an array of all available spacing unit types.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.5.0
	 */
	function ot_recognized_spacing_unit_types( $field_id ) {

		return apply_filters(
			'ot_recognized_spacing_unit_types',
			array(
				'px' => 'px',
				'%'  => '%',
				'em' => 'em',
				'pt' => 'pt',
			),
			$field_id
		);

	}
}

if ( ! function_exists( 'ot_recognized_google_font_families' ) ) {

	/**
	 * Recognized Google font families
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.5.0
	 */
	function ot_recognized_google_font_families( $field_id ) {

		$families        = array();
		$ot_google_fonts = get_theme_mod( 'ot_google_fonts', array() );

		// Forces an array rebuild when we switch themes.
		if ( empty( $ot_google_fonts ) ) {
			$ot_google_fonts = ot_fetch_google_fonts( true, true );
		}

		foreach ( (array) $ot_google_fonts as $key => $item ) {

			if ( isset( $item['family'] ) ) {
				$families[ $key ] = $item['family'];
			}
		}

		return apply_filters( 'ot_recognized_google_font_families', $families, $field_id );
	}
}

if ( ! function_exists( 'ot_recognized_google_font_variants' ) ) {

	/**
	 * Recognized Google font variants
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 * @param string $family   The font family.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.5.0
	 */
	function ot_recognized_google_font_variants( $field_id, $family ) {

		$variants        = array();
		$ot_google_fonts = get_theme_mod( 'ot_google_fonts', array() );

		if ( isset( $ot_google_fonts[ $family ]['variants'] ) ) {
			$variants = $ot_google_fonts[ $family ]['variants'];
		}

		return apply_filters( 'ot_recognized_google_font_variants', $variants, $field_id, $family );
	}
}

if ( ! function_exists( 'ot_recognized_google_font_subsets' ) ) {

	/**
	 * Recognized Google font subsets
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 * @param string $family   The font family.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.5.0
	 */
	function ot_recognized_google_font_subsets( $field_id, $family ) {

		$subsets         = array();
		$ot_google_fonts = get_theme_mod( 'ot_google_fonts', array() );

		if ( isset( $ot_google_fonts[ $family ]['subsets'] ) ) {
			$subsets = $ot_google_fonts[ $family ]['subsets'];
		}

		return apply_filters( 'ot_recognized_google_font_subsets', $subsets, $field_id, $family );
	}
}

if ( ! function_exists( 'ot_measurement_unit_types' ) ) {

	/**
	 * Measurement Units
	 *
	 * Returns an array of all available unit types.
	 * Renamed in version 2.0 to avoid name collisions.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  1.1.8
	 * @since  2.0
	 */
	function ot_measurement_unit_types( $field_id = '' ) {

		return apply_filters(
			'ot_measurement_unit_types',
			array(
				'px' => 'px',
				'%'  => '%',
				'em' => 'em',
				'pt' => 'pt',
			),
			$field_id
		);

	}
}

if ( ! function_exists( 'ot_radio_images' ) ) {

	/**
	 * Radio Images default array.
	 *
	 * Returns an array of all available radio images.
	 * You can filter this function to change the images
	 * on a per option basis.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_radio_images( $field_id ) {

		return apply_filters(
			'ot_radio_images',
			array(
				array(
					'value' => 'left-sidebar',
					'label' => esc_html__( 'Left Sidebar', 'hueman' ),
					'src'   => OT_URL . 'assets/images/layout/left-sidebar.png',
				),
				array(
					'value' => 'right-sidebar',
					'label' => esc_html__( 'Right Sidebar', 'hueman' ),
					'src'   => OT_URL . 'assets/images/layout/right-sidebar.png',
				),
				array(
					'value' => 'full-width',
					'label' => esc_html__( 'Full Width (no sidebar)', 'hueman' ),
					'src'   => OT_URL . 'assets/images/layout/full-width.png',
				),
				array(
					'value' => 'dual-sidebar',
					'label' => esc_html__( 'Dual Sidebar', 'hueman' ),
					'src'   => OT_URL . 'assets/images/layout/dual-sidebar.png',
				),
				array(
					'value' => 'left-dual-sidebar',
					'label' => esc_html__( 'Left Dual Sidebar', 'hueman' ),
					'src'   => OT_URL . 'assets/images/layout/left-dual-sidebar.png',
				),
				array(
					'value' => 'right-dual-sidebar',
					'label' => esc_html__( 'Right Dual Sidebar', 'hueman' ),
					'src'   => OT_URL . 'assets/images/layout/right-dual-sidebar.png',
				),
			),
			$field_id
		);

	}
}

if ( ! function_exists( 'ot_list_item_settings' ) ) {

	/**
	 * Default List Item Settings array.
	 *
	 * Returns an array of the default list item settings.
	 * You can filter this function to change the settings
	 * on a per option basis.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_list_item_settings( $field_id ) {

		$settings = apply_filters(
			'ot_list_item_settings',
			array(
				array(
					'id'        => 'image',
					'label'     => esc_html__( 'Image', 'hueman' ),
					'desc'      => '',
					'std'       => '',
					'type'      => 'upload',
					'rows'      => '',
					'class'     => '',
					'post_type' => '',
					'choices'   => array(),
				),
				array(
					'id'        => 'link',
					'label'     => esc_html__( 'Link', 'hueman' ),
					'desc'      => '',
					'std'       => '',
					'type'      => 'text',
					'rows'      => '',
					'class'     => '',
					'post_type' => '',
					'choices'   => array(),
				),
				array(
					'id'        => 'description',
					'label'     => esc_html__( 'Description', 'hueman' ),
					'desc'      => '',
					'std'       => '',
					'type'      => 'textarea-simple',
					'rows'      => 10,
					'class'     => '',
					'post_type' => '',
					'choices'   => array(),
				),
			),
			$field_id
		);

		return $settings;
	}
}

if ( ! function_exists( 'ot_slider_settings' ) ) {

	/**
	 * Default Slider Settings array.
	 *
	 * Returns an array of the default slider settings.
	 * You can filter this function to change the settings
	 * on a per option basis.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_slider_settings( $field_id ) {

		$settings = apply_filters(
			'image_slider_fields',
			array(
				array(
					'name'  => 'image',
					'type'  => 'image',
					'label' => esc_html__( 'Image', 'hueman' ),
					'class' => '',
				),
				array(
					'name'  => 'link',
					'type'  => 'text',
					'label' => esc_html__( 'Link', 'hueman' ),
					'class' => '',
				),
				array(
					'name'  => 'description',
					'type'  => 'textarea',
					'label' => esc_html__( 'Description', 'hueman' ),
					'class' => '',
				),
			),
			$field_id
		);

		// Fix the array keys, values, and just get it 2.0 ready.
		foreach ( $settings as $_k => $setting ) {

			foreach ( $setting as $s_key => $s_value ) {

				if ( 'name' === $s_key ) {

					$settings[ $_k ]['id'] = $s_value;
					unset( $settings[ $_k ]['name'] );
				} elseif ( 'type' === $s_key ) {

					if ( 'input' === $s_value ) {

						$settings[ $_k ]['type'] = 'text';
					} elseif ( 'textarea' === $s_value ) {

						$settings[ $_k ]['type'] = 'textarea-simple';
					} elseif ( 'image' === $s_value ) {

						$settings[ $_k ]['type'] = 'upload';
					}
				}
			}
		}

		return $settings;
	}
}

if ( ! function_exists( 'ot_social_links_settings' ) ) {

	/**
	 * Default Social Links Settings array.
	 *
	 * Returns an array of the default social links settings.
	 * You can filter this function to change the settings
	 * on a per option basis.
	 *
	 * @uses apply_filters()
	 *
	 * @param string $field_id ID that's passed to the filter.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.4.0
	 */
	function ot_social_links_settings( $field_id ) {

		/* translators: %s: the http protocol */
		$string   = esc_html__( 'Enter a link to the profile or page on the social website. Remember to add the %s part to the front of the link.', 'hueman' );
		$settings = apply_filters(
			'ot_social_links_settings',
			array(
				array(
					'id'    => 'name',
					'label' => esc_html__( 'Name', 'hueman' ),
					'desc'  => esc_html__( 'Enter the name of the social website.', 'hueman' ),
					'std'   => '',
					'type'  => 'text',
					'class' => 'option-tree-setting-title',
				),
				array(
					'id'    => 'title',
					'label' => 'Title',
					'desc'  => esc_html__( 'Enter the text shown in the title attribute of the link.', 'hueman' ),
					'type'  => 'text',
				),
				array(
					'id'    => 'href',
					'label' => 'Link',
					'desc'  => sprintf( $string, '<code>http:// or https://</code>' ),
					'type'  => 'text',
				),
			),
			$field_id
		);

		return $settings;
	}
}

if ( ! function_exists( 'ot_insert_css_with_markers' ) ) {

	/**
	 * Inserts CSS with field_id markers.
	 *
	 * Inserts CSS into a dynamic.css file, placing it between
	 * BEGIN and END field_id markers. Replaces existing marked info,
	 * but still retains surrounding data.
	 *
	 * @param  string $field_id  The CSS option field ID.
	 * @param  string $insertion The current option_tree array.
	 * @param  bool   $meta      Whether or not the value is stored in meta.
	 * @return bool   True on write success, false on failure.
	 *
	 * @access  public
	 * @since   1.1.8
	 * @updated 2.5.3
	 */
	function ot_insert_css_with_markers( $field_id = '', $insertion = '', $meta = false ) {}
}

if ( ! function_exists( 'ot_remove_old_css' ) ) {

	/**
	 * Remove old CSS.
	 *
	 * Removes CSS when the textarea is empty, but still retains surrounding styles.
	 *
	 * @param  string $field_id The CSS option field ID.
	 * @return bool   True on write success, false on failure.
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_remove_old_css( $field_id = '' ) {}
}

if ( ! function_exists( 'ot_normalize_css' ) ) {

	/**
	 * Normalize CSS
	 *
	 * Normalize & Convert all line-endings to UNIX format.
	 *
	 * @param string $css The CSS styles.
	 *
	 * @return string
	 *
	 * @access  public
	 * @since   1.1.8
	 * @updated 2.0
	 */
	function ot_normalize_css( $css ) {

		// Normalize & Convert.
		$css = str_replace( "\r\n", "\n", $css );
		$css = str_replace( "\r", "\n", $css );

		// Don't allow out-of-control blank lines .
		$css = preg_replace( "/\n{2,}/", "\n\n", $css );

		return $css;
	}
}

if ( ! function_exists( 'ot_loop_through_option_types' ) ) {

	/**
	 * Helper function to loop over the option types.
	 *
	 * @param string $type  The current option type.
	 * @param bool   $child Whether of not there are children elements.
	 *
	 * @return string
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_loop_through_option_types( $type = '', $child = false ) {

		$content = '';
		$types   = ot_option_types_array();

		if ( $child ) {
			unset( $types['list-item'] );
		}

		foreach ( $types as $key => $value ) {
			$content .= '<option value="' . esc_attr( $key ) . '" ' . selected( $type, $key, false ) . '>' . esc_html( $value ) . '</option>';
		}

		return $content;

	}
}

if ( ! function_exists( 'ot_loop_through_choices' ) ) {

	/**
	 * Helper function to loop over choices.
	 *
	 * @param string $name The form element name.
	 * @param array  $choices The array of choices.
	 *
	 * @return string
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_loop_through_choices( $name, $choices = array() ) {

		$content = '';

		foreach ( (array) $choices as $key => $choice ) {
			if ( is_array( $choice ) ) {
				$content .= '<li class="ui-state-default list-choice">' . ot_choices_view( $name, $key, $choice ) . '</li>';
			}
		}

		return $content;
	}
}

if ( ! function_exists( 'ot_loop_through_sub_settings' ) ) {

	/**
	 * Helper function to loop over sub settings.
	 *
	 * @param string $name The form element name.
	 * @param array  $settings The array of settings.
	 *
	 * @return string
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_loop_through_sub_settings( $name, $settings = array() ) {

		$content = '';

		foreach ( $settings as $key => $setting ) {
			if ( is_array( $setting ) ) {
				$content .= '<li class="ui-state-default list-sub-setting">' . ot_settings_view( $name, $key, $setting ) . '</li>';
			}
		}

		return $content;
	}
}

if ( ! function_exists( 'ot_sections_view' ) ) {

	/**
	 * Helper function to display sections.
	 *
	 * This function is used in AJAX to add a new section
	 * and when section have already been added and saved.
	 *
	 * @param  string $name    The form element name.
	 * @param  int    $key     The array key for the current element.
	 * @param  array  $section An array of values for the current section.
	 *
	 * @return string
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_sections_view( $name, $key, $section = array() ) {

		/* translators: %s: Section Title emphasized */
		$str_title = esc_html__( '%s: Displayed as a menu item on the Theme Options page.', 'hueman' );

		/* translators: %s: Section ID emphasized */
		$str_id = esc_html__( '%s: A unique lower case alphanumeric string, underscores allowed.', 'hueman' );

		return '
		<div class="option-tree-setting is-section">
			<div class="open">' . ( isset( $section['title'] ) ? esc_attr( $section['title'] ) : 'Section ' . ( absint( $key ) + 1 ) ) . '</div>
			<div class="button-section">
				<a href="javascript:void(0);" class="option-tree-setting-edit option-tree-ui-button button left-item" title="' . esc_html__( 'edit', 'hueman' ) . '">
					<span class="icon ot-icon-pencil"></span>' . esc_html__( 'Edit', 'hueman' ) . '
				</a>
				<a href="javascript:void(0);" class="option-tree-setting-remove option-tree-ui-button button button-secondary light right-item" title="' . esc_html__( 'Delete', 'hueman' ) . '">
					<span class="icon ot-icon-trash-o"></span>' . esc_html__( 'Delete', 'hueman' ) . '
				</a>
			</div>
			<div class="option-tree-setting-body">
				<div class="format-settings">
					<div class="format-setting type-text">
						<div class="description">' . sprintf( $str_title, '<strong>' . esc_html__( 'Section Title', 'hueman' ) . '</strong>', 'option-tree' ) . '</div>
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][title]" value="' . ( isset( $section['title'] ) ? esc_attr( $section['title'] ) : '' ) . '" class="widefat option-tree-ui-input option-tree-setting-title section-title" autocomplete="off" />
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-text">
						<div class="description">' . sprintf( $str_id, '<strong>' . esc_html__( 'Section ID', 'hueman' ) . '</strong>', 'option-tree' ) . '</div>
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][id]" value="' . ( isset( $section['id'] ) ? esc_attr( $section['id'] ) : '' ) . '" class="widefat option-tree-ui-input section-id" autocomplete="off" />
						</div>
					</div>
				</div>
			</div>
		</div>';
	}
}

if ( ! function_exists( 'ot_settings_view' ) ) {

	/**
	 * Helper function to display settings.
	 *
	 * This function is used in AJAX to add a new setting
	 * and when settings have already been added and saved.
	 *
	 * @param  string $name    The form element name.
	 * @param  int    $key     The array key for the current element.
	 * @param  array  $setting An array of values for the current setting.
	 *
	 * @return string
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_settings_view( $name, $key, $setting = array() ) {

		$child    = ( false !== strpos( $name, '][settings]' ) ) ? true : false;
		$type     = isset( $setting['type'] ) ? $setting['type'] : '';
		$std      = isset( $setting['std'] ) ? $setting['std'] : '';
		$operator = isset( $setting['operator'] ) ? esc_attr( $setting['operator'] ) : 'and';

		// Serialize the standard value just in case.
		if ( is_array( $std ) ) {
			$std = maybe_serialize( $std );
		}

		if ( in_array( $type, array( 'css', 'javascript', 'textarea', 'textarea-simple' ), true ) ) {
			$std_form_element = '<textarea class="textarea" rows="10" cols="40" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][std]">' . esc_html( $std ) . '</textarea>';
		} else {
			$std_form_element = '<input type="text" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][std]" value="' . esc_attr( $std ) . '" class="widefat option-tree-ui-input" autocomplete="off" />';
		}

		/* translators: %s: Label emphasized */
		$str_label = esc_html__( '%s: Displayed as the label of a form element on the Theme Options page.', 'hueman' );

		/* translators: %s: ID emphasized */
		$str_id = esc_html__( '%s: A unique lower case alphanumeric string, underscores allowed.', 'hueman' );

		/* translators: %s: Type emphasized */
		$str_type = esc_html__( '%s: Choose one of the available option types from the dropdown.', 'hueman' );

		/* translators: %s: Description emphasized */
		$str_desc = esc_html__( '%s: Enter a detailed description for the users to read on the Theme Options page, HTML is allowed. This is also where you enter content for both the Textblock & Textblock Titled option types.', 'hueman' );

		/* translators: %s: Choices emphasized */
		$str_choices = esc_html__( '%s: This will only affect the following option types: Checkbox, Radio, Select & Select Image.', 'hueman' );

		/* translators: %s: Settings emphasized */
		$str_settings = esc_html__( '%s: This will only affect the List Item option type.', 'hueman' );

		/* translators: %1$s: Standard emphasized, %2$s: visual path to documentation */
		$str_standard = esc_html__( '%1$s: Setting the standard value for your option only works for some option types. Read the %2$s for more information on which ones.', 'hueman' );

		/* translators: %s: Rows emphasized */
		$str_rows = esc_html__( '%s: Enter a numeric value for the number of rows in your textarea. This will only affect the following option types: CSS, Textarea, & Textarea Simple.', 'hueman' );

		/* translators: %s: Post Type emphasized */
		$str_post_type = esc_html__( '%s: Add a comma separated list of post type like \'post,page\'. This will only affect the following option types: Custom Post Type Checkbox, & Custom Post Type Select.', 'hueman' );

		/* translators: %s: Taxonomy emphasized */
		$str_taxonomy = esc_html__( '%s: Add a comma separated list of any registered taxonomy like \'category,post_tag\'. This will only affect the following option types: Taxonomy Checkbox, & Taxonomy Select.', 'hueman' );

		/* translators: %1$s: Min, Max, & Step emphasized, %2$s: format, %3$s: range, %4$s: minimum interval */
		$str_min_max_step = esc_html__( '%1$s: Add a comma separated list of options in the following format %2$s (slide from %3$s in intervals of %4$s). The three values represent the minimum, maximum, and step options and will only affect the Numeric Slider option type.', 'hueman' );

		/* translators: %s: CSS Class emphasized */
		$str_css_class = esc_html__( '%s: Add and optional class to this option type.', 'hueman' );

		/* translators: %1$s: Condition emphasized, %2$s: example value, %3$s: list of valid conditions */
		$str_condition = esc_html__( '%1$s: Add a comma separated list (no spaces) of conditions in which the field will be visible, leave this setting empty to always show the field. In these examples, %2$s is a placeholder for your condition, which can be in the form of %3$s.', 'hueman' );

		/* translators: %s: Operator emphasized */
		$str_operator = esc_html__( '%s: Choose the logical operator to compute the result of the conditions.', 'hueman' );

		return '
		<div class="option-tree-setting">
			<div class="open">' . ( isset( $setting['label'] ) ? esc_attr( $setting['label'] ) : 'Setting ' . ( absint( $key ) + 1 ) ) . '</div>
			<div class="button-section">
				<a href="javascript:void(0);" class="option-tree-setting-edit option-tree-ui-button button left-item" title="' . esc_html__( 'Edit', 'hueman' ) . '">
					<span class="icon ot-icon-pencil"></span>' . esc_html__( 'Edit', 'hueman' ) . '
				</a>
				<a href="javascript:void(0);" class="option-tree-setting-remove option-tree-ui-button button button-secondary light right-item" title="' . esc_html__( 'Delete', 'hueman' ) . '">
					<span class="icon ot-icon-trash-o"></span>' . esc_html__( 'Delete', 'hueman' ) . '
				</a>
			</div>
			<div class="option-tree-setting-body">
				<div class="format-settings">
					<div class="format-setting type-text wide-desc">
						<div class="description">' . sprintf( $str_label, '<strong>' . esc_html__( 'Label', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][label]" value="' . ( isset( $setting['label'] ) ? esc_attr( $setting['label'] ) : '' ) . '" class="widefat option-tree-ui-input option-tree-setting-title" autocomplete="off" />
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-text wide-desc">
						<div class="description">' . sprintf( $str_id, '<strong>' . esc_html__( 'ID', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][id]" value="' . ( isset( $setting['id'] ) ? esc_attr( $setting['id'] ) : '' ) . '" class="widefat option-tree-ui-input" autocomplete="off" />
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-select wide-desc">
						<div class="description">' . sprintf( $str_type, '<strong>' . esc_html__( 'Type', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<select name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][type]" value="' . esc_attr( $type ) . '" class="option-tree-ui-select">
								' . ot_loop_through_option_types( $type, $child ) . '
							</select>
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-textarea wide-desc">
						<div class="description">' . sprintf( $str_desc, '<strong>' . esc_html__( 'Description', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<textarea class="textarea" rows="10" cols="40" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][desc]">' . ( isset( $setting['desc'] ) ? esc_html( $setting['desc'] ) : '' ) . '</textarea>
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-textblock wide-desc">
						<div class="description">' . sprintf( $str_choices, '<strong>' . esc_html__( 'Choices', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<ul class="option-tree-setting-wrap option-tree-sortable" data-name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . ']">
								' . ( isset( $setting['choices'] ) ? ot_loop_through_choices( $name . '[' . $key . ']', $setting['choices'] ) : '' ) . '
							</ul>
							<a href="javascript:void(0);" class="option-tree-choice-add option-tree-ui-button button hug-left">' . esc_html__( 'Add Choice', 'hueman' ) . '</a>
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-textblock wide-desc">
						<div class="description">' . sprintf( $str_settings, '<strong>' . esc_html__( 'Settings', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<ul class="option-tree-setting-wrap option-tree-sortable" data-name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . ']">
								' . ( isset( $setting['settings'] ) ? ot_loop_through_sub_settings( $name . '[' . $key . '][settings]', $setting['settings'] ) : '' ) . '
							</ul>
							<a href="javascript:void(0);" class="option-tree-list-item-setting-add option-tree-ui-button button hug-left">' . esc_html__( 'Add Setting', 'hueman' ) . '</a>
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-text wide-desc">
						<div class="description">' . sprintf( $str_standard, '<strong>' . esc_html__( 'Standard', 'hueman' ) . '</strong>', '<code>' . esc_html__( 'OptionTree->Documentation', 'hueman' ) . '</code>' ) . '</div>
						<div class="format-setting-inner">
							' . $std_form_element . '
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-text wide-desc">
						<div class="description">' . sprintf( $str_rows, '<strong>' . esc_html__( 'Rows', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][rows]" value="' . ( isset( $setting['rows'] ) ? esc_attr( $setting['rows'] ) : '' ) . '" class="widefat option-tree-ui-input" />
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-text wide-desc">
						<div class="description">' . sprintf( $str_post_type, '<strong>' . esc_html__( 'Post Type', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][post_type]" value="' . ( isset( $setting['post_type'] ) ? esc_attr( $setting['post_type'] ) : '' ) . '" class="widefat option-tree-ui-input" autocomplete="off" />
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-text wide-desc">
						<div class="description">' . sprintf( $str_taxonomy, '<strong>' . esc_html__( 'Taxonomy', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][taxonomy]" value="' . ( isset( $setting['taxonomy'] ) ? esc_attr( $setting['taxonomy'] ) : '' ) . '" class="widefat option-tree-ui-input" autocomplete="off" />
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-text wide-desc">
						<div class="description">' . sprintf( $str_min_max_step, '<strong>' . esc_html__( 'Min, Max, & Step', 'hueman' ) . '</strong>', '<code>0,100,1</code>', '<code>0-100</code>', '<code>1</code>' ) . '</div>
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][min_max_step]" value="' . ( isset( $setting['min_max_step'] ) ? esc_attr( $setting['min_max_step'] ) : '' ) . '" class="widefat option-tree-ui-input" autocomplete="off" />
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-text wide-desc">
						<div class="description">' . sprintf( $str_css_class, '<strong>' . esc_html__( 'CSS Class', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][class]" value="' . ( isset( $setting['class'] ) ? esc_attr( $setting['class'] ) : '' ) . '" class="widefat option-tree-ui-input" autocomplete="off" />
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-text wide-desc">
						<div class="description">' . sprintf( $str_condition, '<strong>' . esc_html__( 'Condition', 'hueman' ) . '</strong>', '<code>value</code>', '<code>field_id:is(value)</code>, <code>field_id:not(value)</code>, <code>field_id:contains(value)</code>, <code>field_id:less_than(value)</code>, <code>field_id:less_than_or_equal_to(value)</code>, <code>field_id:greater_than(value)</code>, or <code>field_id:greater_than_or_equal_to(value)</code>' ) . '</div>
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][condition]" value="' . ( isset( $setting['condition'] ) ? esc_attr( $setting['condition'] ) : '' ) . '" class="widefat option-tree-ui-input" autocomplete="off" />
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-select wide-desc">
						<div class="description">' . sprintf( $str_operator, '<strong>' . esc_html__( 'Operator', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<select name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][operator]" value="' . esc_attr( $operator ) . '" class="option-tree-ui-select">
								<option value="and" ' . selected( $operator, 'and', false ) . '>' . esc_html__( 'and', 'hueman' ) . '</option>
								<option value="or" ' . selected( $operator, 'or', false ) . '>' . esc_html__( 'or', 'hueman' ) . '</option>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
		' . ( ! $child ? '<input type="hidden" class="hidden-section" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][section]" value="' . ( isset( $setting['section'] ) ? esc_attr( $setting['section'] ) : '' ) . '" />' : '' );
	}
}

if ( ! function_exists( 'ot_choices_view' ) ) {

	/**
	 * Helper function to display setting choices.
	 *
	 * This function is used in AJAX to add a new choice
	 * and when choices have already been added and saved.
	 *
	 * @param  string $name   The form element name.
	 * @param  int    $key    The array key for the current element.
	 * @param  array  $choice An array of values for the current choice.
	 *
	 * @return string
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_choices_view( $name, $key, $choice = array() ) {

		return '
		<div class="option-tree-setting">
			<div class="open">' . ( isset( $choice['label'] ) ? esc_attr( $choice['label'] ) : 'Choice ' . ( absint( $key ) + 1 ) ) . '</div>
			<div class="button-section">
				<a href="javascript:void(0);" class="option-tree-setting-edit option-tree-ui-button button left-item" title="' . esc_html__( 'Edit', 'hueman' ) . '">
					<span class="icon ot-icon-pencil"></span>' . esc_html__( 'Edit', 'hueman' ) . '
				</a>
				<a href="javascript:void(0);" class="option-tree-setting-remove option-tree-ui-button button button-secondary light right-item" title="' . esc_html__( 'Delete', 'hueman' ) . '">
					<span class="icon ot-icon-trash-o"></span>' . esc_html__( 'Delete', 'hueman' ) . '
				</a>
			</div>
			<div class="option-tree-setting-body">
				<div class="format-settings">
					<div class="format-setting-label">
						<h5>' . esc_html__( 'Label', 'hueman' ) . '</h5>
					</div>
					<div class="format-setting type-text wide-desc">
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[choices][' . esc_attr( $key ) . '][label]" value="' . ( isset( $choice['label'] ) ? esc_attr( $choice['label'] ) : '' ) . '" class="widefat option-tree-ui-input option-tree-setting-title" autocomplete="off" />
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting-label">
						<h5>' . esc_html__( 'Value', 'hueman' ) . '</h5>
					</div>
					<div class="format-setting type-text wide-desc">
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[choices][' . esc_attr( $key ) . '][value]" value="' . ( isset( $choice['value'] ) ? esc_attr( $choice['value'] ) : '' ) . '" class="widefat option-tree-ui-input" autocomplete="off" />
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting-label">
						<h5>' . esc_html__( 'Image Source (Radio Image only)', 'hueman' ) . '</h5>
					</div>
					<div class="format-setting type-text wide-desc">
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[choices][' . esc_attr( $key ) . '][src]" value="' . ( isset( $choice['src'] ) ? esc_attr( $choice['src'] ) : '' ) . '" class="widefat option-tree-ui-input" autocomplete="off" />
						</div>
					</div>
				</div>
			</div>
		</div>';

	}
}

if ( ! function_exists( 'ot_contextual_help_view' ) ) {

	/**
	 * Helper function to display sections.
	 *
	 * This function is used in AJAX to add a new section
	 * and when section have already been added and saved.
	 *
	 * @param  string $name    The name/ID of the help page.
	 * @param  int    $key     The array key for the current element.
	 * @param  array  $content An array of values for the current section.
	 *
	 * @return string
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_contextual_help_view( $name, $key, $content = array() ) {

		/* translators: %s: Title emphasized */
		$str_title = esc_html__( '%s: Displayed as a contextual help menu item on the Theme Options page.', 'hueman' );

		/* translators: %s: ID emphasized */
		$str_id = esc_html__( '%s: A unique lower case alphanumeric string, underscores allowed.', 'hueman' );

		/* translators: %s: Content emphasized */
		$str_content = esc_html__( '%s: Enter the HTML content about this contextual help item displayed on the Theme Option page for end users to read.', 'hueman' );

		return '
		<div class="option-tree-setting">
			<div class="open">' . ( isset( $content['title'] ) ? esc_attr( $content['title'] ) : 'Content ' . ( absint( $key ) + 1 ) ) . '</div>
			<div class="button-section">
				<a href="javascript:void(0);" class="option-tree-setting-edit option-tree-ui-button button left-item" title="' . esc_html__( 'Edit', 'hueman' ) . '">
					<span class="icon ot-icon-pencil"></span>' . esc_html__( 'Edit', 'hueman' ) . '
				</a>
				<a href="javascript:void(0);" class="option-tree-setting-remove option-tree-ui-button button button-secondary light right-item" title="' . esc_html__( 'Delete', 'hueman' ) . '">
					<span class="icon ot-icon-trash-o"></span>' . esc_html__( 'Delete', 'hueman' ) . '
				</a>
			</div>
			<div class="option-tree-setting-body">
				<div class="format-settings">
					<div class="format-setting type-text no-desc">
						<div class="description">' . sprintf( $str_title, '<strong>' . esc_html__( 'Title', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][title]" value="' . ( isset( $content['title'] ) ? esc_attr( $content['title'] ) : '' ) . '" class="widefat option-tree-ui-input option-tree-setting-title" autocomplete="off" />
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-text no-desc">
						<div class="description">' . sprintf( $str_id, '<strong>' . esc_html__( 'ID', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<input type="text" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][id]" value="' . ( isset( $content['id'] ) ? esc_attr( $content['id'] ) : '' ) . '" class="widefat option-tree-ui-input" autocomplete="off" />
						</div>
					</div>
				</div>
				<div class="format-settings">
					<div class="format-setting type-textarea no-desc">
						<div class="description">' . sprintf( $str_content, '<strong>' . esc_html__( 'Content', 'hueman' ) . '</strong>' ) . '</div>
						<div class="format-setting-inner">
							<textarea class="textarea" rows="15" cols="40" name="' . esc_attr( $name ) . '[' . esc_attr( $key ) . '][content]">' . ( isset( $content['content'] ) ? esc_textarea( $content['content'] ) : '' ) . '</textarea>
						</div>
					</div>
				</div>
			</div>
		</div>';

	}
}

if ( ! function_exists( 'ot_layout_view' ) ) {

	/**
	 * Helper function to display sections.
	 *
	 * @param  string $key           Layout ID.
	 * @param  string $data          Layout encoded value.
	 * @param  string $active_layout Active layout ID.
	 *
	 * @return string
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_layout_view( $key, $data = '', $active_layout = '' ) {

		return '
		<div class="option-tree-setting">
			<div class="open">' . ( isset( $key ) ? esc_attr( $key ) : esc_html__( 'Layout', 'hueman' ) ) . '</div>
			<div class="button-section">
				<a href="javascript:void(0);" class="option-tree-layout-activate option-tree-ui-button button left-item' . ( $active_layout === $key ? ' active' : '' ) . '" title="' . esc_html__( 'Activate', 'hueman' ) . '">
					<span class="icon ot-icon-square-o"></span>' . esc_html__( 'Activate', 'hueman' ) . '
				</a>
				<a href="javascript:void(0);" class="option-tree-setting-remove option-tree-ui-button button button-secondary light right-item" title="' . esc_html__( 'Delete', 'hueman' ) . '">
					<span class="icon ot-icon-trash-o"></span>' . esc_html__( 'Delete', 'hueman' ) . '
				</a>
			</div>
			<input type="hidden" name="' . esc_attr( ot_layouts_id() ) . '[' . esc_attr( $key ) . ']" value="' . esc_attr( $data ) . '" />
		</div>';
	}
}



if ( ! function_exists( 'ot_theme_options_layouts_form' ) ) {

	/**
	 * Helper function to display Theme Options layouts form.
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_theme_options_layouts_form() {

		echo '<form method="post" id="option-tree-options-layouts-form">';

		// Form nonce.
		wp_nonce_field( 'option_tree_modify_layouts_form', 'option_tree_modify_layouts_nonce' );

		// Get the saved layouts.
		$layouts = get_option( ot_layouts_id() );

		// Set active layout.
		$active_layout = isset( $layouts['active_layout'] ) ? $layouts['active_layout'] : '';

		if ( is_array( $layouts ) && 1 < count( $layouts ) ) {

			$active_layout = $layouts['active_layout'];

			echo '<input type="hidden" id="the_current_layout" value="' . esc_attr( $active_layout ) . '" />';

			echo '<div class="option-tree-active-layout">';

			echo '<select name="' . esc_attr( ot_layouts_id() ) . '[active_layout]" class="option-tree-ui-select">';

			$hidden = '';

			foreach ( $layouts as $key => $data ) {

				if ( 'active_layout' === $key ) {
					continue;
				}

				echo '<option ' . selected( $key, $active_layout, false ) . ' value="' . esc_attr( $key ) . '">' . esc_attr( $key ) . '</option>';
				$hidden_safe .= '<input type="hidden" name="' . esc_attr( ot_layouts_id() ) . '[' . esc_attr( $key ) . ']" value="' . esc_attr( isset( $data ) ? $data : '' ) . '" />';
			}

			echo '</select>';

			echo '</div>';

			echo $hidden_safe; // phpcs:ignore
		}

		/* new layout wrapper */
		echo '<div class="option-tree-save-layout' . ( ! empty( $active_layout ) ? ' active-layout' : '' ) . '">';

		/* add new layout */
		echo '<input type="text" name="' . esc_attr( ot_layouts_id() ) . '[_add_new_layout_]" value="" class="widefat option-tree-ui-input" autocomplete="off" />';

		echo '<button type="submit" class="option-tree-ui-button button button-primary save-layout" title="' . esc_html__( 'New Layout', 'hueman' ) . '">' . esc_html__( 'New Layout', 'hueman' ) . '</button>';

		echo '</div>';

		echo '</form>';
	}
}

if ( ! function_exists( 'ot_sanitize_option_id' ) ) {

	/**
	 * Helper function to sanitize the option ID's.
	 *
	 * @param  string $input The string to sanitize.
	 * @return string
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_sanitize_option_id( $input ) {
		return preg_replace( '/[^a-z0-9]/', '_', trim( strtolower( $input ) ) );
	}
}

if ( ! function_exists( 'ot_sanitize_layout_id' ) ) {

	/**
	 * Helper function to sanitize the layout ID's.
	 *
	 * @param  string $input The string to sanitize.
	 * @return string
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_sanitize_layout_id( $input ) {
		return preg_replace( '/[^a-z0-9]/', '-', trim( strtolower( $input ) ) );
	}
}

if ( ! function_exists( 'ot_convert_array_to_string' ) ) {

	/**
	 * Convert choices array to string.
	 *
	 * @param array $input The array to convert to a string.
	 *
	 * @return bool|string
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_convert_array_to_string( $input ) {

		if ( is_array( $input ) ) {

			foreach ( $input as $k => $choice ) {
				$choices[ $k ] = $choice['value'] . '|' . $choice['label'];

				if ( isset( $choice['src'] ) ) {
					$choices[ $k ] .= '|' . $choice['src'];
				}
			}

			return implode( ',', $choices );
		}

		return false;
	}
}

if ( ! function_exists( 'ot_convert_string_to_array' ) ) {

	/**
	 * Convert choices string to array.
	 *
	 * @param  string $input The string to convert to an array.
	 *
	 * @return bool|array
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_convert_string_to_array( $input ) {

		if ( '' !== $input ) {

			// Empty choices array.
			$choices = array();

			// Exlode the string into an array.
			foreach ( explode( ',', $input ) as $k => $choice ) {

				// If ":" is splitting the string go deeper.
				if ( preg_match( '/\|/', $choice ) ) {
					$split = explode( '|', $choice );

					if ( 2 > count( $split ) ) {
						continue;
					}

					$choices[ $k ]['value'] = trim( $split[0] );
					$choices[ $k ]['label'] = trim( $split[1] );

					// If radio image there are three values.
					if ( isset( $split[2] ) ) {
						$choices[ $k ]['src'] = trim( $split[2] );
					}
				} else {
					$choices[ $k ]['value'] = trim( $choice );
					$choices[ $k ]['label'] = trim( $choice );
				}
			}

			// Return a formatted choices array.
			return $choices;
		}

		return false;
	}
}

if ( ! function_exists( 'ot_strpos_array' ) ) {

	/**
	 * Helper function - strpos() in array recursively.
	 *
	 * @param  string $haystack The string to search in.
	 * @param  array  $needles  Keys to search for.
	 * @return bool
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_strpos_array( $haystack, $needles = array() ) {

		foreach ( $needles as $needle ) {
			if ( false !== strpos( $haystack, $needle ) ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'ot_array_keys_exists' ) ) {

	/**
	 * Helper function - array_key_exists() recursively.
	 *
	 * @param  array $haystack The array to search in.
	 * @param  array $needles  Keys to search for.
	 * @return bool
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_array_keys_exists( $haystack, $needles = array() ) {

		foreach ( $needles as $k ) {
			if ( isset( $haystack[ $k ] ) ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'ot_stripslashes' ) ) {

	/**
	 * Custom stripslashes from single value or array.
	 *
	 * @param  mixed $input The string or array to stripslashes from.
	 * @return mixed
	 *
	 * @access public
	 * @since  2.0
	 */
	function ot_stripslashes( $input ) {

		if ( is_array( $input ) ) {

			foreach ( $input as &$val ) {

				if ( is_array( $val ) ) {
					$val = ot_stripslashes( $val );
				} else {
					$val = stripslashes( trim( $val ) );
				}
			}
		} else {
			$input = stripslashes( trim( $input ) );
		}

		return $input;
	}
}

if ( ! function_exists( 'ot_reverse_wpautop' ) ) {

	/**
	 * Reverse wpautop.
	 *
	 * @param  string $string The string to be filtered.
	 * @return string
	 *
	 * @access public
	 * @since  2.0.9
	 */
	function ot_reverse_wpautop( $string = '' ) {

		// Return if string is empty.
		if ( '' === trim( $string ) ) {
			return '';
		}

		// Remove all new lines & <p> tags.
		$string = str_replace( array( "\n", '<p>' ), '', $string );

		// Replace <br /> with \r.
		$string = str_replace( array( '<br />', '<br>', '<br/>' ), "\r", $string );

		// Replace </p> with \r\n.
		$string = str_replace( '</p>', "\r\n", $string );

		// Return clean string.
		return trim( $string );
	}
}

if ( ! function_exists( 'ot_range' ) ) {

	/**
	 * Returns an array of elements from start to limit, inclusive.
	 *
	 * Occasionally zero will be some impossibly large number to
	 * the "E" power when creating a range from negative to positive.
	 * This function attempts to fix that by setting that number back to "0".
	 *
	 * @param  string $start First value of the sequence.
	 * @param  string $limit The sequence is ended upon reaching the limit value.
	 * @param  int    $step  If a step value is given, it will be used as the increment
	 *                       between elements in the sequence. step should be given as a
	 *                       positive number. If not specified, step will default to 1.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.0.12
	 */
	function ot_range( $start, $limit, $step = 1 ) {

		if ( $step < 0 ) {
			$step = 1;
		}

		$range = range( $start, $limit, $step );

		foreach ( $range as $k => $v ) {
			if ( strpos( $v, 'E' ) ) {
				$range[ $k ] = 0;
			}
		}

		return $range;
	}
}


if ( ! function_exists( 'ot_filter_std_value' ) ) {

	/**
	 * Helper function to filter standard option values.
	 *
	 * @param  mixed $value Saved string or array value.
	 * @param  mixed $std   Standard string or array value.
	 *
	 * @return mixed String or array.
	 *
	 * @access public
	 * @since  2.0.15
	 */
	function ot_filter_std_value( $value = '', $std = '' ) {
		if ( is_string( $std ) && ! empty( $std ) ) {

			// Search for an array.
			preg_match( '/a:\d+:{.*?}/', $std, $array_matches, PREG_OFFSET_CAPTURE, 0 );

			// Search for an object.
			preg_match( '/O:\d+:"[a-z0-9_]+":\d+:{.*?}/i', $std, $obj_matches, PREG_OFFSET_CAPTURE, 0 );

			// Prevent object injection.
			if ( $array_matches && ! $obj_matches ) {
				$std = maybe_unserialize( $std );
			} elseif ( $obj_matches ) {
				$std = '';
			}
		}

		if ( is_array( $value ) && is_array( $std ) ) {
			foreach ( $value as $k => $v ) {
				if ( '' === $value[ $k ] && isset( $std[ $k ] ) ) {
					$value[ $k ] = $std[ $k ];
				}
			}
		} elseif ( '' === $value && ! empty( $std ) ) {
			$value = $std;
		}

		return $value;
	}
}

if ( ! function_exists( 'ot_set_google_fonts' ) ) {

	/**
	 * Helper function to set the Google fonts array.
	 *
	 * @param string $id    The option ID.
	 * @param bool   $value The option value.
	 *
	 * @access public
	 * @since  2.5.0
	 */
	function ot_set_google_fonts( $id = '', $value = '' ) {

		$ot_set_google_fonts = get_theme_mod( 'ot_set_google_fonts', array() );

		if ( is_array( $value ) && ! empty( $value ) ) {
			$ot_set_google_fonts[ $id ] = $value;
		} elseif ( isset( $ot_set_google_fonts[ $id ] ) ) {
			unset( $ot_set_google_fonts[ $id ] );
		}

		set_theme_mod( 'ot_set_google_fonts', $ot_set_google_fonts );
	}
}

if ( ! function_exists( 'ot_update_google_fonts_after_save' ) ) {

	/**
	 * Helper function to remove unused options from the Google fonts array.
	 *
	 * @param array $options The array of saved options.
	 *
	 * @access public
	 * @since  2.5.0
	 */
	function ot_update_google_fonts_after_save( $options = array() ) {

		$ot_set_google_fonts = get_theme_mod( 'ot_set_google_fonts', array() );

		foreach ( $ot_set_google_fonts as $key => $set ) {
			if ( ! isset( $options[ $key ] ) ) {
				unset( $ot_set_google_fonts[ $key ] );
			}
		}
		set_theme_mod( 'ot_set_google_fonts', $ot_set_google_fonts );
	}

	add_action( 'ot_after_theme_options_save', 'ot_update_google_fonts_after_save', 1 );
}

if ( ! function_exists( 'ot_fetch_google_fonts' ) ) {

	/**
	 * Helper function to fetch the Google fonts array.
	 *
	 * @param bool $normalize Whether or not to return a normalized array. Default 'true'.
	 * @param bool $force_rebuild Whether or not to force the array to be rebuilt. Default 'false'.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.5.0
	 */
	function ot_fetch_google_fonts( $normalize = true, $force_rebuild = false ) {

		// Google Fonts cache key.
		$ot_google_fonts_cache_key = apply_filters( 'ot_google_fonts_cache_key', 'ot_google_fonts_cache' );

		// Get the fonts from cache.
		$ot_google_fonts = apply_filters( 'ot_google_fonts_cache', get_transient( $ot_google_fonts_cache_key ) );

		if ( $force_rebuild || ! is_array( $ot_google_fonts ) || empty( $ot_google_fonts ) ) {

			$ot_google_fonts = array();

			// API url and key.
			$ot_google_fonts_api_url = apply_filters( 'ot_google_fonts_api_url', 'https://www.googleapis.com/webfonts/v1/webfonts' );
			$ot_google_fonts_api_key = apply_filters( 'ot_google_fonts_api_key', false );

			if ( false === $ot_google_fonts_api_key ) {
				return array();
			}

			// API arguments.
			$ot_google_fonts_fields = apply_filters(
				'ot_google_fonts_fields',
				array(
					'family',
					'variants',
					'subsets',
				)
			);
			$ot_google_fonts_sort   = apply_filters( 'ot_google_fonts_sort', 'alpha' );

			// Initiate API request.
			$ot_google_fonts_query_args = array(
				'key'    => $ot_google_fonts_api_key,
				'fields' => 'items(' . implode( ',', $ot_google_fonts_fields ) . ')',
				'sort'   => $ot_google_fonts_sort,
			);

			// Build and make the request.
			$ot_google_fonts_query    = esc_url_raw( add_query_arg( $ot_google_fonts_query_args, $ot_google_fonts_api_url ) );
			$ot_google_fonts_response = wp_safe_remote_get(
				$ot_google_fonts_query,
				array(
					'sslverify' => false,
					'timeout'   => 15,
				)
			);

			// Continue if we got a valid response.
			if ( 200 === wp_remote_retrieve_response_code( $ot_google_fonts_response ) ) {

				$response_body = wp_remote_retrieve_body( $ot_google_fonts_response );

				if ( $response_body ) {

					// JSON decode the response body and cache the result.
					$ot_google_fonts_data = json_decode( trim( $response_body ), true );

					if ( is_array( $ot_google_fonts_data ) && isset( $ot_google_fonts_data['items'] ) ) {

						$ot_google_fonts = $ot_google_fonts_data['items'];

						// Normalize the array key.
						$ot_google_fonts_tmp = array();
						foreach ( $ot_google_fonts as $key => $value ) {
							if ( ! isset( $value['family'] ) ) {
								continue;
							}

							$id = preg_replace( '/[^a-z0-9_\-]/', '', strtolower( remove_accents( $value['family'] ) ) );

							if ( $id ) {
								$ot_google_fonts_tmp[ $id ] = $value;
							}
						}

						$ot_google_fonts = $ot_google_fonts_tmp;
						set_theme_mod( 'ot_google_fonts', $ot_google_fonts );
						set_transient( $ot_google_fonts_cache_key, $ot_google_fonts, WEEK_IN_SECONDS );
					}
				}
			}
		}

		return $normalize ? ot_normalize_google_fonts( $ot_google_fonts ) : $ot_google_fonts;
	}
}

if ( ! function_exists( 'ot_normalize_google_fonts' ) ) {

	/**
	 * Helper function to normalize the Google fonts array.
	 *
	 * @param array $google_fonts An array of fonts to normalize.
	 *
	 * @return array
	 *
	 * @access public
	 * @since  2.5.0
	 */
	function ot_normalize_google_fonts( $google_fonts ) {

		$ot_normalized_google_fonts = array();

		if ( is_array( $google_fonts ) && ! empty( $google_fonts ) ) {

			foreach ( $google_fonts as $google_font ) {

				if ( isset( $google_font['family'] ) ) {

					$id = str_replace( ' ', '+', $google_font['family'] );

					$ot_normalized_google_fonts[ $id ] = array(
						'family' => $google_font['family'],
					);

					if ( isset( $google_font['variants'] ) ) {
						$ot_normalized_google_fonts[ $id ]['variants'] = $google_font['variants'];
					}

					if ( isset( $google_font['subsets'] ) ) {
						$ot_normalized_google_fonts[ $id ]['subsets'] = $google_font['subsets'];
					}
				}
			}
		}

		return $ot_normalized_google_fonts;
	}
}

if ( ! function_exists( 'ot_wpml_register_string' ) ) {

	/**
	 * Helper function to register a WPML string.
	 *
	 * @param string $id    The string ID.
	 * @param string $value The string value.
	 *
	 * @access public
	 * @since  2.1
	 */
	function ot_wpml_register_string( $id, $value ) {
		if ( function_exists( 'icl_register_string' ) ) {
			icl_register_string( 'Theme Options', $id, $value );
		}
	}
}

if ( ! function_exists( 'ot_wpml_unregister_string' ) ) {

	/**
	 * Helper function to unregister a WPML string.
	 *
	 * @param string $id The string ID.
	 *
	 * @access public
	 * @since  2.1
	 */
	function ot_wpml_unregister_string( $id ) {
		if ( function_exists( 'icl_unregister_string' ) ) {
			icl_unregister_string( 'Theme Options', $id );
		}
	}
}

if ( ! function_exists( 'ot_maybe_migrate_settings' ) ) {

	/**
	 * Maybe migrate Settings.
	 *
	 * @access public
	 * @since  2.3.3
	 */
	function ot_maybe_migrate_settings() {

		// Filter the ID to migrate from.
		$settings_id = apply_filters( 'ot_migrate_settings_id', '' );

		// Attempt to migrate Settings.
		if ( ! empty( $settings_id ) && false === get_option( ot_settings_id() ) && ot_settings_id() !== $settings_id ) {

			// Old settings.
			$settings = get_option( $settings_id );

			// Check for array keys.
			if ( isset( $settings['sections'] ) && isset( $settings['settings'] ) ) {
				update_option( ot_settings_id(), $settings );
			}
		}
	}
}

if ( ! function_exists( 'ot_maybe_migrate_options' ) ) {

	/**
	 * Maybe migrate Option.
	 *
	 * @access public
	 * @since  2.3.3
	 */
	function ot_maybe_migrate_options() {

		// Filter the ID to migrate from.
		$options_id = apply_filters( 'ot_migrate_options_id', '' );

		// Attempt to migrate Theme Options.
		if ( ! empty( $options_id ) && false === get_option( ot_options_id() ) && ot_options_id() !== $options_id ) {

			// Old options.
			$options = get_option( $options_id );

			// Migrate to new ID.
			update_option( ot_options_id(), $options );
		}
	}
}

if ( ! function_exists( 'ot_maybe_migrate_layouts' ) ) {

	/**
	 * Maybe migrate Layouts.
	 *
	 * @access public
	 * @since  2.3.3
	 */
	function ot_maybe_migrate_layouts() {

		// Filter the ID to migrate from.
		$layouts_id = apply_filters( 'ot_migrate_layouts_id', '' );

		// Attempt to migrate Layouts.
		if ( ! empty( $layouts_id ) && false === get_option( ot_layouts_id() ) && ot_layouts_id() !== $layouts_id ) {

			// Old options.
			$layouts = get_option( $layouts_id );

			// Migrate to new ID.
			update_option( ot_layouts_id(), $layouts );
		}
	}
}

if ( ! function_exists( 'ot_meta_box_post_format_gallery' ) ) {

	/**
	 * Returns an array with the post format gallery meta box.
	 *
	 * @param mixed $pages Excepts a comma separated string or array of
	 *                     post_types and is what tells the metabox where to
	 *                     display. Default 'post'.
	 * @return array
	 *
	 * @access public
	 * @since  2.4.0
	 */
	function ot_meta_box_post_format_gallery( $pages = 'post' ) {

		if ( ! current_theme_supports( 'post-formats' ) || ! in_array( 'gallery', current( get_theme_support( 'post-formats' ) ), true ) ) {
			return false;
		}

		if ( is_string( $pages ) ) {
			$pages = explode( ',', $pages );
		}

		return apply_filters(
			'ot_meta_box_post_format_gallery',
			array(
				'id'       => 'ot-post-format-gallery',
				'title'    => esc_html__( 'Gallery', 'hueman' ),
				'desc'     => '',
				'pages'    => $pages,
				'context'  => 'side',
				'priority' => 'low',
				'fields'   => array(
					array(
						'id'    => '_format_gallery',
						'label' => '',
						'desc'  => '',
						'std'   => '',
						'type'  => 'gallery',
						'class' => 'ot-gallery-shortcode',
					),
				),
			),
			$pages
		);
	}
}

if ( ! function_exists( 'ot_meta_box_post_format_link' ) ) {

	/**
	 * Returns an array with the post format link metabox.
	 *
	 * @param mixed $pages Excepts a comma separated string or array of
	 *                     post_types and is what tells the metabox where to
	 *                     display. Default 'post'.
	 * @return array
	 *
	 * @access public
	 * @since  2.4.0
	 */
	function ot_meta_box_post_format_link( $pages = 'post' ) {

		if ( ! current_theme_supports( 'post-formats' ) || ! in_array( 'link', current( get_theme_support( 'post-formats' ) ), true ) ) {
			return false;
		}

		if ( is_string( $pages ) ) {
			$pages = explode( ',', $pages );
		}

		return apply_filters(
			'ot_meta_box_post_format_link',
			array(
				'id'       => 'ot-post-format-link',
				'title'    => esc_html__( 'Link', 'hueman' ),
				'desc'     => '',
				'pages'    => $pages,
				'context'  => 'side',
				'priority' => 'low',
				'fields'   => array(
					array(
						'id'    => '_format_link_url',
						'label' => '',
						'desc'  => esc_html__( 'Link URL', 'hueman' ),
						'std'   => '',
						'type'  => 'text',
					),
					array(
						'id'    => '_format_link_title',
						'label' => '',
						'desc'  => esc_html__( 'Link Title', 'hueman' ),
						'std'   => '',
						'type'  => 'text',
					),
				),
			),
			$pages
		);
	}
}

if ( ! function_exists( 'ot_meta_box_post_format_quote' ) ) {

	/**
	 * Returns an array with the post format quote metabox.
	 *
	 * @param mixed $pages Excepts a comma separated string or array of
	 *                     post_types and is what tells the metabox where to
	 *                     display. Default 'post'.
	 * @return array
	 *
	 * @access public
	 * @since  2.4.0
	 */
	function ot_meta_box_post_format_quote( $pages = 'post' ) {

		if ( ! current_theme_supports( 'post-formats' ) || ! in_array( 'quote', current( get_theme_support( 'post-formats' ) ), true ) ) {
			return false;
		}

		if ( is_string( $pages ) ) {
			$pages = explode( ',', $pages );
		}

		return apply_filters(
			'ot_meta_box_post_format_quote',
			array(
				'id'       => 'ot-post-format-quote',
				'title'    => esc_html__( 'Quote', 'hueman' ),
				'desc'     => '',
				'pages'    => $pages,
				'context'  => 'side',
				'priority' => 'low',
				'fields'   => array(
					array(
						'id'    => '_format_quote_source_name',
						'label' => '',
						'desc'  => esc_html__( 'Source Name (ex. author, singer, actor)', 'hueman' ),
						'std'   => '',
						'type'  => 'text',
					),
					array(
						'id'    => '_format_quote_source_url',
						'label' => '',
						'desc'  => esc_html__( 'Source URL', 'hueman' ),
						'std'   => '',
						'type'  => 'text',
					),
					array(
						'id'    => '_format_quote_source_title',
						'label' => '',
						'desc'  => esc_html__( 'Source Title (ex. book, song, movie)', 'hueman' ),
						'std'   => '',
						'type'  => 'text',
					),
					array(
						'id'    => '_format_quote_source_date',
						'label' => '',
						'desc'  => esc_html__( 'Source Date', 'hueman' ),
						'std'   => '',
						'type'  => 'text',
					),
				),
			),
			$pages
		);

	}
}

if ( ! function_exists( 'ot_meta_box_post_format_video' ) ) {

	/**
	 * Returns an array with the post format video metabox.
	 *
	 * @param mixed $pages Excepts a comma separated string or array of
	 *                     post_types and is what tells the metabox where to
	 *                     display. Default 'post'.
	 * @return array
	 *
	 * @access public
	 * @since  2.4.0
	 */
	function ot_meta_box_post_format_video( $pages = 'post' ) {

		if ( ! current_theme_supports( 'post-formats' ) || ! in_array( 'video', current( get_theme_support( 'post-formats' ) ), true ) ) {
			return false;
		}

		if ( is_string( $pages ) ) {
			$pages = explode( ',', $pages );
		}

		/* translators: %1$s: link to WorPress Codex, %2$s: video shortcode */
		$string = esc_html__( 'Embed video from services like Youtube, Vimeo, or Hulu. You can find a list of supported oEmbed sites in the %1$s. Alternatively, you could use the built-in %2$s shortcode.', 'hueman' );

		return apply_filters(
			'ot_meta_box_post_format_video',
			array(
				'id'       => 'ot-post-format-video',
				'title'    => __( 'Video', 'hueman' ),
				'desc'     => '',
				'pages'    => $pages,
				'context'  => 'side',
				'priority' => 'low',
				'fields'   => array(
					array(
						'id'    => '_format_video_embed',
						'label' => '',
						'desc'  => sprintf( $string, '<a href="https://codex.wordpress.org/Embeds" target="_blank">' . esc_html__( 'WordPress Codex', 'hueman' ) . '</a>', '<code>[video]</code>' ),
						'std'   => '',
						'type'  => 'textarea',
					),
				),
			),
			$pages
		);
	}
}

if ( ! function_exists( 'ot_meta_box_post_format_audio' ) ) {

	/**
	 * Returns an array with the post format audio metabox.
	 *
	 * @param mixed $pages Excepts a comma separated string or array of
	 *                     post_types and is what tells the metabox where to
	 *                     display. Default 'post'.
	 * @return array
	 *
	 * @access public
	 * @since  2.4.0
	 */
	function ot_meta_box_post_format_audio( $pages = 'post' ) {

		if ( ! current_theme_supports( 'post-formats' ) || ! in_array( 'audio', current( get_theme_support( 'post-formats' ) ), true ) ) {
			return false;
		}

		if ( is_string( $pages ) ) {
			$pages = explode( ',', $pages );
		}

		/* translators: %1$s: link to WorPress Codex, %2$s: audio shortcode */
		$string = esc_html__( 'Embed audio from services like SoundCloud and Radio. You can find a list of supported oEmbed sites in the %1$s. Alternatively, you could use the built-in %2$s shortcode.', 'hueman' );

		return apply_filters(
			'ot_meta_box_post_format_audio',
			array(
				'id'       => 'ot-post-format-audio',
				'title'    => esc_html__( 'Audio', 'hueman' ),
				'desc'     => '',
				'pages'    => $pages,
				'context'  => 'side',
				'priority' => 'low',
				'fields'   => array(
					array(
						'id'    => '_format_audio_embed',
						'label' => '',
						'desc'  => sprintf( $string, '<a href="https://codex.wordpress.org/Embeds" target="_blank">' . esc_html__( 'WordPress Codex', 'hueman' ) . '</a>', '<code>[audio]</code>' ),
						'std'   => '',
						'type'  => 'textarea',
					),
				),
			),
			$pages
		);

	}
}

if ( ! function_exists( 'ot_get_option_type_by_id' ) ) {

	/**
	 * Returns the option type by ID.
	 *
	 * @param  string $option_id The option ID.
	 * @param  string $settings_id The settings array ID.
	 * @return string The option type.
	 *
	 * @access public
	 * @since  2.4.2
	 */
	function ot_get_option_type_by_id( $option_id, $settings_id = '' ) {

		if ( empty( $settings_id ) ) {
			$settings_id = ot_settings_id();
		}

		$settings = get_option( $settings_id, array() );

		if ( isset( $settings['settings'] ) ) {

			foreach ( $settings['settings'] as $value ) {

				if ( $option_id === $value['id'] && isset( $value['type'] ) ) {
					return $value['type'];
				}
			}
		}

		return false;
	}
}

if ( ! function_exists( '_ot_settings_potential_shared_terms' ) ) {

	/**
	 * Build an array of potential Theme Options that could share terms.
	 *
	 * @return array
	 *
	 * @access private
	 * @since  2.5.4
	 */
	function _ot_settings_potential_shared_terms() {

		$options      = array();
		$settings     = get_option( ot_settings_id(), array() );
		$option_types = array(
			'category-checkbox',
			'category-select',
			'tag-checkbox',
			'tag-select',
			'taxonomy-checkbox',
			'taxonomy-select',
		);

		if ( isset( $settings['settings'] ) ) {

			foreach ( $settings['settings'] as $value ) {

				if ( isset( $value['type'] ) ) {

					if ( 'list-item' === $value['type'] && isset( $value['settings'] ) ) {

						$saved = ot_get_option( $value['id'] );

						foreach ( $value['settings'] as $item ) {

							if ( isset( $value['id'] ) && isset( $item['type'] ) && in_array( $item['type'], $option_types, true ) ) {
								$sub_options = array();

								foreach ( $saved as $sub_key => $sub_value ) {
									if ( isset( $sub_value[ $item['id'] ] ) ) {
										$sub_options[ $sub_key ] = $sub_value[ $item['id'] ];
									}
								}

								if ( ! empty( $sub_options ) ) {
									$options[] = array(
										'id'       => $item['id'],
										'taxonomy' => $value['taxonomy'],
										'parent'   => $value['id'],
										'value'    => $sub_options,
									);
								}
							}
						}
					}

					if ( in_array( $value['type'], $option_types, true ) ) {
						$saved = ot_get_option( $value['id'] );
						if ( ! empty( $saved ) ) {
							$options[] = array(
								'id'       => $value['id'],
								'taxonomy' => $value['taxonomy'],
								'value'    => $saved,
							);
						}
					}
				}
			}
		}

		return $options;
	}
}

if ( ! function_exists( '_ot_meta_box_potential_shared_terms' ) ) {

	/**
	 * Build an array of potential Meta Box options that could share terms.
	 *
	 * @return array
	 *
	 * @access private
	 * @since  2.5.4
	 */
	function _ot_meta_box_potential_shared_terms() {
		global $ot_meta_boxes;

		$options      = array();
		$settings     = $ot_meta_boxes;
		$option_types = array(
			'category-checkbox',
			'category-select',
			'tag-checkbox',
			'tag-select',
			'taxonomy-checkbox',
			'taxonomy-select',
		);

		foreach ( $settings as $setting ) {

			if ( isset( $setting['fields'] ) ) {

				foreach ( $setting['fields'] as $value ) {

					if ( isset( $value['type'] ) ) {

						if ( 'list-item' === $value['type'] && isset( $value['settings'] ) ) {

							$children = array();

							foreach ( $value['settings'] as $item ) {

								if ( isset( $value['id'] ) && isset( $item['type'] ) && in_array( $item['type'], $option_types, true ) ) {
									$children[ $value['id'] ][] = $item['id'];
								}
							}

							if ( ! empty( $children[ $value['id'] ] ) ) {
								$options[] = array(
									'id'       => $value['id'],
									'children' => $children[ $value['id'] ],
									'taxonomy' => $value['taxonomy'],
								);
							}
						}

						if ( in_array( $value['type'], $option_types, true ) ) {
							$options[] = array(
								'id'       => $value['id'],
								'taxonomy' => $value['taxonomy'],
							);
						}
					}
				}
			}
		}

		return $options;
	}
}

if ( ! function_exists( 'ot_split_shared_term' ) ) {

	/**
	 * Update terms when a term gets split.
	 *
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 *
	 * @access public
	 * @since  2.5.4
	 */
	function ot_split_shared_term( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {
		unset( $term_taxonomy_id );

		// Process the Theme Options.
		$settings    = _ot_settings_potential_shared_terms();
		$old_options = get_option( ot_options_id(), array() );
		$new_options = $old_options;

		// Process the saved settings.
		if ( ! empty( $settings ) && ! empty( $old_options ) ) {

			// Loop over the Theme Options.
			foreach ( $settings as $option ) {

				if ( ! is_array( $option['taxonomy'] ) ) {
					$option['taxonomy'] = explode( ',', $option['taxonomy'] );
				}

				if ( ! in_array( $taxonomy, $option['taxonomy'], true ) ) {
					continue;
				}

				// The option ID was found.
				if ( array_key_exists( $option['id'], $old_options ) || ( isset( $option['parent'] ) && array_key_exists( $option['parent'], $old_options ) ) ) {

					// This is a list item, we have to go deeper.
					if ( isset( $option['parent'] ) ) {

						// Loop over the array.
						foreach ( $option['value'] as $key => $value ) {

							// The value is an array of IDs.
							if ( is_array( $value ) ) {

								// Loop over the sub array.
								foreach ( $value as $sub_key => $sub_value ) {

									if ( $sub_value === $term_id ) {
										unset( $new_options[ $option['parent'] ][ $key ][ $option['id'] ][ $sub_key ] );
										$new_options[ $option['parent'] ][ $key ][ $option['id'] ][ $new_term_id ] = $new_term_id;
									}
								}
							} elseif ( $value === $term_id ) {
								unset( $new_options[ $option['parent'] ][ $key ][ $option['id'] ] );
								$new_options[ $option['parent'] ][ $key ][ $option['id'] ] = $new_term_id;
							}
						}
					} else {

						// The value is an array of IDs.
						if ( is_array( $option['value'] ) ) {

							// Loop over the array.
							foreach ( $option['value'] as $key => $value ) {

								// It's a single value, just replace it.
								if ( $value === $term_id ) {
									unset( $new_options[ $option['id'] ][ $key ] );
									$new_options[ $option['id'] ][ $new_term_id ] = $new_term_id;
								}
							}

							// It's a single value, just replace it.
						} elseif ( $option['value'] === $term_id ) {
							$new_options[ $option['id'] ] = $new_term_id;
						}
					}
				}
			}
		}

		// Options need to be updated.
		if ( $old_options !== $new_options ) {
			update_option( ot_options_id(), $new_options );
		}

		// Process the Meta Boxes.
		$meta_settings = _ot_meta_box_potential_shared_terms();

		if ( ! empty( $meta_settings ) ) {

			foreach ( $meta_settings as $option ) {

				if ( ! is_array( $option['taxonomy'] ) ) {
					$option['taxonomy'] = explode( ',', $option['taxonomy'] );
				}

				if ( ! in_array( $taxonomy, $option['taxonomy'], true ) ) {
					continue;
				}

				if ( isset( $option['children'] ) ) {
					$post_ids = get_posts(
						array(
							'fields'   => 'ids',
							'meta_key' => $option['id'], // phpcs:ignore
						)
					);

					if ( $post_ids ) {

						foreach ( $post_ids as $post_id ) {

							// Get the meta.
							$old_meta = get_post_meta( $post_id, $option['id'], true );
							$new_meta = $old_meta;

							// Has a saved value.
							if ( ! empty( $old_meta ) && is_array( $old_meta ) ) {

								// Loop over the array.
								foreach ( $old_meta as $key => $value ) {

									foreach ( $value as $sub_key => $sub_value ) {

										if ( in_array( $sub_key, $option['children'], true ) ) {

											// The value is an array of IDs.
											if ( is_array( $sub_value ) ) {

												// Loop over the array.
												foreach ( $sub_value as $sub_sub_key => $sub_sub_value ) {

													// It's a single value, just replace it.
													if ( $sub_sub_value === $term_id ) {
														unset( $new_meta[ $key ][ $sub_key ][ $sub_sub_key ] );
														$new_meta[ $key ][ $sub_key ][ $new_term_id ] = $new_term_id;
													}
												}

												// It's a single value, just replace it.
											} elseif ( $sub_value === $term_id ) {
												$new_meta[ $key ][ $sub_key ] = $new_term_id;
											}
										}
									}
								}

								// Update.
								if ( $old_meta !== $new_meta ) {
									update_post_meta( $post_id, $option['id'], $new_meta, $old_meta );
								}
							}
						}
					}
				} else {
					$post_ids = get_posts(
						array(
							'fields'     => 'ids',
							'meta_query' => array( // phpcs:ignore
								'key'     => $option['id'],
								'value'   => $term_id,
								'compare' => 'IN',
							),
						)
					);

					if ( $post_ids ) {

						foreach ( $post_ids as $post_id ) {

							// Get the meta.
							$old_meta = get_post_meta( $post_id, $option['id'], true );
							$new_meta = $old_meta;

							// Has a saved value.
							if ( ! empty( $old_meta ) ) {

								// The value is an array of IDs.
								if ( is_array( $old_meta ) ) {

									// Loop over the array.
									foreach ( $old_meta as $key => $value ) {

										// It's a single value, just replace it.
										if ( $value === $term_id ) {
											unset( $new_meta[ $key ] );
											$new_meta[ $new_term_id ] = $new_term_id;
										}
									}

									// It's a single value, just replace it.
								} elseif ( $old_meta === $term_id ) {
									$new_meta = $new_term_id;
								}

								// Update.
								if ( $old_meta !== $new_meta ) {
									update_post_meta( $post_id, $option['id'], $new_meta, $old_meta );
								}
							}
						}
					}
				}
			}
		}
	}

	add_action( 'split_shared_term', 'ot_split_shared_term', 10, 4 );
}
