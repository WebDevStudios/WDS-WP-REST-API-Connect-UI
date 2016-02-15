module.exports = function( grunt ) {

	require('load-grunt-tasks')(grunt);

	var pkg = grunt.file.readJSON( 'package.json' );

	var bannerTemplate = '/**\n' +
		' * <%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' +
		' * <%= pkg.author.url %>\n' +
		' *\n' +
		' * Copyright (c) <%= grunt.template.today("yyyy") %>;\n' +
		' * Licensed GPLv2+\n' +
		' */\n';

	var compactBannerTemplate = '/** ' +
		'<%= pkg.title %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %> | <%= pkg.author.url %> | Copyright (c) <%= grunt.template.today("yyyy") %>; | Licensed GPLv2+' +
		' **/\n';

	// Project configuration
	grunt.initConfig( {

		pkg: pkg,


		watch:  {
			styles: {
				files: ['assets/**/*.css','assets/**/*.scss'],
				tasks: ['styles'],
				options: {
					spawn: false,
					livereload: true,
					debounceDelay: 500
				}
			},
			scripts: {
				files: ['assets/**/*.js'],
				tasks: ['scripts'],
				options: {
					spawn: false,
					livereload: true,
					debounceDelay: 500
				}
			},
			php: {
				files: ['**/*.php', '!vendor/**.*.php'],
				tasks: ['php'],
				options: {
					spawn: false,
					debounceDelay: 500
				}
			}
		},

		makepot: {
			dist: {
				options: {
					domainPath: '/languages/',
					potFilename: pkg.name + '.pot',
					type: 'wp-plugin'
				}
			}
		},

		addtextdomain: {
			dist: {
				options: {
					textdomain: pkg.name
				},
				target: {
					files: {
						src: ['**/*.php']
					}
				}
			}
		},

		// Command line commands
		exec: {
			makezip: 'echo "Updating .zip file. Please hold..." && grunt compress',
		},

		// make a zipfile
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: 'wds-rest-connect-ui.zip'
				},
				files: [ {
						expand: true,
						// cwd: '/',
						src: [
							'**',
							'!**/**dandelion**.yml',
							'!**/phpunit.xml',
							'!**/package.json',
							'!**/node_modules/**',
							'!**/bin/**',
							'!**/tests/**',
							'!**/sass/**',
							'!**.zip',
							'!**/**.orig',
							'!**/**.map',
							'!**/**Gruntfile.js',
							'!vendor/league/oauth1-client/resources/**',
							'!vendor/rmccue/requests/docs/**',
							'!vendor/rmccue/requests/examples/**',
							'!vendor/symfony/event-dispatcher/Tests/**',
							'!vendor/guzzle/guzzle/phing/**',
							'!vendor/guzzle/guzzle/docs/**'
						],
						dest: '/'
				} ]
			}
		},

		githooks: {
			all: {
				// create zip and deploy changes to ftp
				'pre-push': 'compress exec'
			}
		}
	} );

	// Default task.
	grunt.registerTask( 'scripts', [] );
	grunt.registerTask( 'styles', [] );
	grunt.registerTask( 'php', [ 'addtextdomain', 'makepot' ] );
	grunt.registerTask( 'default', ['styles', 'scripts', 'php', 'compress'] );

	grunt.util.linefeed = '\n';
};
