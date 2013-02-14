jQuery(document).ready(function()
                       {
                           var media = wp.media.editor.add('content');

                           media.on('attach',
                                    function()
                                    {
                                        if(typeof wp.media.view.Galleriapress == "undefined")
                                        {
                                            wp.media.view.Galleriapress = wp.media.View.extend({
                                                tagname: 'div',
                                                className: 'galleriapress',
		                                            template:  wp.media.template('galleriapress'),
                                            
                                                render: function()
                                                {
                                                    this.$el.html( this.template() );
                                                    return this;
                                                }
                                            });
                                        }

                                        wp.media.controller.Galleriapress = wp.media.controller.State.extend({
		                                        defaults: {
			                                          id:         'galleriapress',
			                                          multiple:   false, // false, 'add', 'reset'
			                                          describe:   false,
			                                          toolbar:    'select',
			                                          sidebar:    'settings',
			                                          content:    'galleriapress',
			                                          router:     'browse',
			                                          menu:       'default',
			                                          searchable: true,
			                                          filterable: false,
			                                          sortable:   true,
                                                priority:   200,
			                                          title:      'Galleriapress',

			                                          // Uses a user setting to override the content mode.
			                                          contentUserSetting: true,

			                                          // Sync the selection from the last state when 'multiple' matches.
			                                          syncSelection: true
		                                        }
                                        });

                                        this.states.add(new wp.media.controller.Galleriapress());

                                        this.on('content:render:galleriapress',
                                                function () 
                                                {
                                                    this.$el.addClass('hide-router');

			                                              var view = new wp.media.view.Galleriapress(
                                                        {
				                                                    controller: this,
				                                                    model:      this.state()
			                                                  }).render();
                                                    
			                                              this.content.set( view );
			                                              view.url.focus();

                                                    console.log("render"); 
                                                },
                                                this);

                                        this.menu.render();

                                    }, media);

                           media.on('content:activate:galleriapress',
                                    function()
                                    {

                                    },
                                    media);


                       });