<?php

	// If this file is called directly, abort.
	if ( ! defined( 'WPINC' ) ) {
		die;
	}
	if ( class_exists( 'WPCF7_FormTag' ) ) {
		class  WPCF7_FormTag_CBXWoocf7orders extends WPCF7_FormTag {
			/**
			 * Better Get Default option method
			 *
			 * @param null   $default
			 * @param string $args
			 *
			 * @return array|int|null|string
			 */
			public function get_default_option( $default = null, $args = '' ) {
				$args = wp_parse_args( $args, array(
					'multiple' => false,
					'shifted'  => false,
				) );

				$options = (array) $this->get_option( 'default' );

				$values = array();

				if ( $default != null ) {

					if ( $args['multiple'] ) {
						if ( $default == '' ) {
							return array();
						}

						if ( preg_match( '/^[0-9_]+$/', $default ) ) {
							$default = explode( '_', $default );
						} else {
							$default = (array) $default;
						}

						$default_temp = array_unique( $default );
						$default      = array();
						foreach ( $default_temp as $num ) {
							$default[] = absint( $num );

						}
					} else {
						$default = intval( $default );

					}


					return $default;

				}

				foreach ( $options as $opt ) {
					$opt = sanitize_key( $opt );

					if ( 'user_' == substr( $opt, 0, 5 ) && is_user_logged_in() ) {
						$primary_props = array( 'user_login', 'user_email', 'user_url' );
						$opt           = in_array( $opt, $primary_props ) ? $opt : substr( $opt, 5 );

						$user      = wp_get_current_user();
						$user_prop = $user->get( $opt );

						if ( ! empty( $user_prop ) ) {
							if ( $args['multiple'] ) {
								$values[] = $user_prop;
							} else {
								return $user_prop;
							}
						}

					} elseif ( 'post_meta' == $opt && in_the_loop() ) {
						if ( $args['multiple'] ) {
							$values = array_merge( $values,
								get_post_meta( get_the_ID(), $this->name ) );
						} else {
							$val = (string) get_post_meta( get_the_ID(), $this->name, true );

							if ( strlen( $val ) ) {
								return $val;
							}
						}

					} elseif ( 'get' == $opt && isset( $_GET[ $this->name ] ) ) {
						$vals = (array) $_GET[ $this->name ];
						$vals = array_map( 'wpcf7_sanitize_query_var', $vals );

						if ( $args['multiple'] ) {
							$values = array_merge( $values, $vals );
						} else {
							$val = isset( $vals[0] ) ? (string) $vals[0] : '';

							if ( strlen( $val ) ) {
								return $val;
							}
						}

					} elseif ( 'post' == $opt && isset( $_POST[ $this->name ] ) ) {
						$vals = (array) $_POST[ $this->name ];
						$vals = array_map( 'wpcf7_sanitize_query_var', $vals );

						if ( $args['multiple'] ) {
							$values = array_merge( $values, $vals );
						} else {
							$val = isset( $vals[0] ) ? (string) $vals[0] : '';

							if ( strlen( $val ) ) {
								return $val;
							}
						}

					} elseif ( 'shortcode_attr' == $opt ) {
						if ( $contact_form = WPCF7_ContactForm::get_current() ) {
							$val = $contact_form->shortcode_attr( $this->name );

							if ( strlen( $val ) ) {
								if ( $args['multiple'] ) {
									$values[] = $val;
								} else {
									return $val;
								}
							}
						}

					} elseif ( preg_match( '/^[0-9_]+$/', $opt ) ) {
						$nums = explode( '_', $opt );

						foreach ( $nums as $num ) {
							$num = absint( $num );
							$num = $args['shifted'] ? $num : $num - 1;

							if ( isset( $this->values[ $num ] ) ) {
								if ( $args['multiple'] ) {
									$values[] = $this->values[ $num ];
								} else {
									return $this->values[ $num ];
								}
							}
						}
					}
				}

				if ( $args['multiple'] ) {
					$values = array_unique( $values );

					return $values;
				} else {
					return $default;
				}
			}
		}//end class WPCF7_FormTag_CBXWoocf7orders
	}