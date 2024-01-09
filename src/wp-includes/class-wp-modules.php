<?php
/**
 * Modules API: WP_Modules class.
 *
 * Native support for ES Modules and Import Maps.
 *
 * @package WordPress
 * @subpackage Modules
 */

/**
 * Core class used to register modules.
 *
 * @since 6.5.0
 */
class WP_Modules {
	/**
	 * Holds the registered modules, keyed by module identifier.
	 *
	 * @since 6.5.0
	 * @var array
	 */
	private $registered = array();

	/**
	 * Holds the module identifiers that were enqueued before registered.
	 *
	 * @since 6.5.0
	 * @var array
	 */
	private $enqueued_before_registered = array();

	/**
	 * Registers the module if no module with that module identifier has already
	 * been registered.
	 *
	 * @since 6.5.0
	 *
	 * @param string                                                        $module_id The identifier of the module.
	 *                                                                                 Should be unique. It will be used
	 *                                                                                 in the final import map.
	 * @param string                                                        $src       Full URL of the module, or path of
	 *                                                                                 the module relative to the
	 *                                                                                 WordPress root directory.
	 * @param array<string|array{id: string, import?: 'static'|'dynamic' }> $deps      Optional. An array of module
	 *                                                                                 identifiers of the dependencies of
	 *                                                                                 this module. The dependencies can
	 *                                                                                 be strings or arrays. If they are
	 *                                                                                 arrays, they need an `id` key with
	 *                                                                                 the module identifier, and can
	 *                                                                                 contain an `import` key with either
	 *                                                                                 `static` or `dynamic`. By default,
	 *                                                                                 dependencies that don't contain an
	 *                                                                                 `import` key are considered static.
	 * @param string|false|null                                             $version   Optional. String specifying the
	 *                                                                                 module version number. Defaults to
	 *                                                                                 false. It is added to the URL as a
	 *                                                                                 query string for cache busting
	 *                                                                                 purposes. If $version is set to
	 *                                                                                 false, the version number is the
	 *                                                                                 currently installed WordPress
	 *                                                                                 version. If $version is set to
	 *                                                                                 null, no version is added.
	 */
	public function register( $module_id, $src, $deps = array(), $version = false ) {
		if ( ! isset( $this->registered[ $module_id ] ) ) {
			$dependencies = array();
			foreach ( $deps as $dependency ) {
				if ( is_array( $dependency ) ) {
					if ( ! isset( $dependency['id'] ) ) {
						_doing_it_wrong( __METHOD__, __( 'Missing required id key in entry among dependencies array.' ), '6.5.0' );
						continue;
					}
					$dependencies[] = array(
						'id'     => $dependency['id'],
						'import' => isset( $dependency['import'] ) && 'dynamic' === $dependency['import'] ? 'dynamic' : 'static',
					);
				} elseif ( is_string( $dependency ) ) {
					$dependencies[] = array(
						'id'     => $dependency,
						'import' => 'static',
					);
				} else {
					_doing_it_wrong( __METHOD__, __( 'Entries in dependencies array must be either strings or arrays with an id key.' ), '6.5.0' );
				}
			}

			$this->registered[ $module_id ] = array(
				'src'          => $src,
				'version'      => $version,
				'enqueue'      => isset( $this->enqueued_before_registered[ $module_id ] ),
				'dependencies' => $dependencies,
				'enqueued'     => false,
				'preloaded'    => false,
			);
		}
	}

	/**
	 * Marks the module to be enqueued in the page the next time
	 * `prints_enqueued_modules` is called.
	 *
	 * @since 6.5.0
	 *
	 * @param string $module_id The identifier of the module.
	 */
	public function enqueue( $module_id ) {
		if ( isset( $this->registered[ $module_id ] ) ) {
			$this->registered[ $module_id ]['enqueue'] = true;
		} else {
			$this->enqueued_before_registered[ $module_id ] = true;
		}
	}

	/**
	 * Unmarks the module so it will no longer be enqueued in the page.
	 *
	 * @since 6.5.0
	 *
	 * @param string $module_id The identifier of the module.
	 */
	public function dequeue( $module_id ) {
		if ( isset( $this->registered[ $module_id ] ) ) {
			$this->registered[ $module_id ]['enqueue'] = false;
		}
		unset( $this->enqueued_before_registered[ $module_id ] );
	}

	/**
	 * Adds the hooks to print the import map and enqueued modules.
	 *
	 * It adds the actions to print the enqueued modules and module preloads to
	 * both `wp_head` and `wp_footer` because in classic themes, the modules
	 * used by the theme and plugins will likely be able to be printed in the
	 * `head`, but the ones used by the blocks will need to be enqueued in the
	 * `footer`.
	 *
	 * As all modules are deferred and dependencies are handled by the browser,
	 * the order of the modules is not important, but it's still better to print
	 * the ones that are available when the `wp_head` is rendered, so the browser
	 * starts downloading those as soon as possible.
	 *
	 * The import map is also printed in the footer to be able to include the
	 * dependencies of all the modules, including the ones printed in the footer.
	 *
	 * @since 6.5.0
	 */
	public function add_hooks() {
		add_action( 'wp_head', array( $this, 'print_enqueued_modules' ) );
		add_action( 'wp_head', array( $this, 'print_module_preloads' ) );
		add_action( 'wp_footer', array( $this, 'print_enqueued_modules' ) );
		add_action( 'wp_footer', array( $this, 'print_module_preloads' ) );
		add_action( 'wp_footer', array( $this, 'print_import_map' ) );
	}

	/**
	 * Returns the import map array.
	 *
	 * @since 6.5.0
	 *
	 * @return array Array with an `imports` key mapping to an array of module identifiers and their respective URLs,
	 *               including the version query.
	 */
	private function get_import_map() {
		$imports = array();
		foreach ( $this->get_dependencies( array_keys( $this->get_marked_for_enqueue() ) ) as $module_id => $module ) {
			$imports[ $module_id ] = $module['src'] . $this->get_version_query_string( $module['version'] );
		}
		return array( 'imports' => $imports );
	}

	/**
	 * Prints the import map using a script tag with a type="importmap" attribute.
	 *
	 * @since 6.5.0
	 */
	public function print_import_map() {
		$import_map = $this->get_import_map();
		if ( ! empty( $import_map['imports'] ) ) {
			wp_print_inline_script_tag(
				wp_json_encode( $import_map, JSON_HEX_TAG | JSON_HEX_AMP ),
				array(
					'type' => 'importmap',
				)
			);
		}
	}

	/**
	 * Prints the enqueued modules using script tags with type="module"
	 * attributes.
	 *
	 * If a enqueued module has already been printed, it will not be printed again
	 * on subsequent calls to this function.
	 *
	 * @since 6.5.0
	 */
	public function print_enqueued_modules() {
		foreach ( $this->get_marked_for_enqueue() as $module_id => $module ) {
			if ( false === $module['enqueued'] ) {
				// Mark it as enqueued so it doesn't get enqueued again.
				$this->registered[ $module_id ]['enqueued'] = true;

				wp_print_script_tag(
					array(
						'type' => 'module',
						'src'  => $module['src'] . $this->get_version_query_string( $module['version'] ),
						'id'   => $module_id,
					)
				);
			}
		}
	}

	/**
	 * Prints the the static dependencies of the enqueued modules using link tags
	 * with rel="modulepreload" attributes.
	 *
	 * If a module is marked for enqueue, it will not be preloaded. If a preloaded
	 * module has already been printed, it will not be printed again on subsequent
	 * calls to this function.
	 *
	 * @since 6.5.0
	 */
	public function print_module_preloads() {
		foreach ( $this->get_dependencies( array_keys( $this->get_marked_for_enqueue() ), array( 'static' ) ) as $module_id => $module ) {
			// Don't preload if it's marked for enqueue or has already been preloaded.
			if ( true !== $module['enqueue'] && false === $module['preloaded'] ) {
				// Mark it as preloaded so it doesn't get preloaded again.
				$this->registered[ $module_id ]['preloaded'] = true;

				echo sprintf(
					'<link rel="modulepreload" href="%s" id="%s">',
					esc_attr( $module['src'] . $this->get_version_query_string( $module['version'] ) ),
					esc_attr( $module_id )
				);
			}
		}
	}

	/**
	 * Gets the version of a module.
	 *
	 * If $version is set to false, the version number is the currently installed
	 * WordPress version. If $version is set to null, no version is added.
	 * Otherwise, the string passed in $version is used.
	 *
	 * @since 6.5.0
	 *
	 * @param string|false|null $version The version of the module.
	 * @return string A string with the version, prepended by `?ver=`, or an empty string if there is no version.
	 */
	private function get_version_query_string( $version ) {
		if ( false === $version ) {
			return '?ver=' . get_bloginfo( 'version' );
		} elseif ( null !== $version ) {
			return '?ver=' . $version;
		}
		return '';
	}

	/**
	 * Retrieves the list of modules marked for enqueue.
	 *
	 * @since 6.5.0
	 *
	 * @return array Modules marked for enqueue, keyed by module identifier.
	 */
	private function get_marked_for_enqueue() {
		$enqueued = array();
		foreach ( $this->registered as $module_id => $module ) {
			if ( true === $module['enqueue'] ) {
				$enqueued[ $module_id ] = $module;
			}
		}
		return $enqueued;
	}

	/**
	 * Retrieves all the dependencies for the given module identifiers, filtered
	 * by import types.
	 *
	 * It will consolidate an array containing a set of unique dependencies based
	 * on the requested import types: 'static', 'dynamic', or both. This method is
	 * recursive and also retrieves dependencies of the dependencies.
	 *
	 * @since 6.5.0
	 *
	 * @param array $module_ids The identifiers of the modules for which to gather dependencies.
	 * @param array $import_types       Optional. Import types of dependencies to retrieve: 'static', 'dynamic', or both.
	 *                                  Default is both.
	 * @return array List of dependencies, keyed by module identifier.
	 */
	private function get_dependencies( $module_ids, $import_types = array( 'static', 'dynamic' ) ) {
		return array_reduce(
			$module_ids,
			function ( $dependency_modules, $module_id ) use ( $import_types ) {
				$dependencies = array();
				foreach ( $this->registered[ $module_id ]['dependencies'] as $dependency ) {
					if (
					in_array( $dependency['import'], $import_types, true ) &&
					isset( $this->registered[ $dependency['id'] ] ) &&
					! isset( $dependency_modules[ $dependency['id'] ] )
					) {
						$dependencies[ $dependency['id'] ] = $this->registered[ $dependency['id'] ];
					}
				}
				return array_merge( $dependency_modules, $dependencies, $this->get_dependencies( array_keys( $dependencies ), $import_types ) );
			},
			array()
		);
	}
}
