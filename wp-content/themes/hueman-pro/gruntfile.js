
module.exports = function(grunt) {
	var path = require('path');
  var _ = require('lodash');
	var global_config = {
		// path to task.js files, defaults to grunt dir
      configPath: path.join(process.cwd(), '__grunt-tasks-config__/'),
      // auto grunt.initConfig
      init: true,
      // data passed into config ( => the basic grunt.initConfig(config) ). Can be used afterwards with < %= test % >
      data: {
        pkg: grunt.file.readJSON( 'package.json' ),
        paths : {
          skop_php : 'addons/skop/',
          front_assets : 'assets/front/',
          addons_assets : 'addons/assets/',
          pro_header_front_assets : 'addons/pro/infinite/front/assets/',
          infinite_front_assets : 'addons/pro/infinite/front/assets/',
          czr_assets : 'addons/assets/czr/',
          lang: 'languages/'
        },
        vars : {
          'textdomain' : 'hueman-pro'
        },
        //https://www.npmjs.org/package/grunt-ssh
        //Check if the context var is set and == travis => avoid travis error with ftpauth no found
        //credentials : 'travis' == grunt.option('context') ? {} : grunt.file.readJSON('.ftpauth'),
        hueman_pro_tasks : {
          //final build meta task
          'pro_build' :  [
              'comments',
              'replace:style',
              'replace:readme_md',
              'replace:readme_txt',
              'clean',
              'copy:main',
              'addtextdomain',
              'makepot',
              'replace:lang',
              'potomo',
              'copy:on_translation_ready_copy_lang_files_in_repo',
              'compress'
          ]
        },
        uglify_requested_paths : {
          src : '' || grunt.option('src'),
          dest : '' || grunt.option('dest')
        }
      }
	};

	// LOAD GRUNT PACKAGES AND CONFIGS
	// https://www.npmjs.org/package/load-grunt-config
	require( 'load-grunt-config' )( grunt , global_config );

	//http://www.thomasboyt.com/2013/09/01/maintainable-grunt.html
	//http://gruntjs.com/api/grunt.task#grunt.task.loadtasks
	//grunt.loadTasks('grunt-tasks');
	// REGISTER TASKS
  _.map( grunt.config('hueman_pro_tasks'), function(task, name) {
    grunt.registerTask(name, task);
  });
};