module.exports = function(grunt) {
    'use strict';

    // Load Grunt tasks automatically
    require('load-grunt-tasks')(grunt, { scope: ['devDependencies', 'dependencies'] });

    /* config grunt
    ------------------------------------------------- */
    grunt.initConfig({
        project: {
            dist: {
                icons:  "dist/icons/",
                sprite: "dist/sprite/",
            },
            icons:      "assets/icons/",
        },
        watch: {
            sprite: {
                files: [ '<%=project.icons%>**/*.svg' ],
                tasks: [ 'svg_sprite', 'newer:svgmin' ]
            }
        },


        /* images
        ------------------------------------------------- */
        svg_sprite: {
            options: {
                svg: {
                    xmlDeclaration: false,
                    doctypeDeclaration: false,
                    namespaceClassnames: false
                }
            },
            target: {
                expand: true,
                cwd: '<%=project.icons%>',
                src: ['**/*.svg'],
                dest: '<%=project.dist.sprite%>',

                options: {
                    mode: {
                        symbol: {
                            dest: '',
                            sprite: 'sprite.svg',
                            render: {
                                // custom example
                                'html': {
                                    template: '<%=project.icons%>/_icons.html'
                                },
                                'php': {
                                    template: '<%=project.icons%>/_list.html'
                                }
                            },
                        },
                    },
                }
            }
        },
        svgmin: {
            options:{
                js2svg: {
                    pretty: true
                },
                plugins: [
                    { removeViewBox: false },
                    { removeUselessStrokeAndFill: false },
                ]
            },
            sprite: {
                options: {
                    plugins: [
                        { removeEmptyAttrs: false },
                        { cleanupIDs: false },
                    ]
                },
                files: {
                    '<%=project.dist.sprite%>sprite.svg': '<%=project.dist.sprite%>sprite.svg'
                }
            }
        },
    });

    /* register tasks
    ------------------------------------------------- */
    grunt.registerTask( 'default', ['watch'] );

    grunt.registerTask( 'build', [
        'svg_sprite',
        'newer:svgmin',
    ]);
};