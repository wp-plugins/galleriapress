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

                                                events:
                                                {
                                                    'click .gallery' : 'gallery_click',
                                                },

                                                gallery_click: function(e)
                                                {
                                                    var gallery = jQuery(e.currentTarget);
                                                    var was_selected = gallery.hasClass('active');

                                                    this.model.set('gallery_id', gallery.data('gallery_id'));
                                                    this.$el.find('.galleriapress-galleries .active').removeClass('active');

                                                    if(!was_selected)
                                                        gallery.addClass('active');
                                                },
                               
                                                render: function()
                                                {
                                                    this.$el.html( this.template() );
                                                    return this;
                                                }
                                            });
                                        }

                                        wp.media.view.Toolbar.GalleriapressToolbar = wp.media.view.Toolbar.extend({
	                                          initialize: function() {
		                                            _.defaults( this.options, {
		                                                event: 'galleriapress_insert_event',
		                                                close: false,
			                                              items: {
			                                                  galleriapress_insert_event: {
			                                                      text: "Insert",
			                                                      style: 'primary',
			                                                      priority: 80,
			                                                      requires: false,
			                                                      click: this.insert_action
			                                                  }
			                                              }
		                                            });
                                                
		                                            wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );
	                                          },
                                            
                                            // called each time the model changes
	                                          refresh: function() {
		                                            wp.media.view.Toolbar.prototype.refresh.apply( this, arguments );
	                                          },
	                                          
	                                          // triggered when the button is clicked
	                                          insert_action: function() {
	                                              this.controller.state().insert_action();
	                                          }
                                        });

                                        wp.media.controller.Galleriapress = wp.media.controller.State.extend({
		                                        defaults: {
			                                          id:         'galleriapress',
			                                          multiple:   false, // false, 'add', 'reset'
			                                          describe:   false,
			                                          toolbar:    'galleriapress-insert',
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
		                                        },

                                            initialize: function()
                                            {
                                                this.props = new Backbone.Model({ gallery_id: 0 });
                                            },

                                            insert_action: function(e)
                                            {
                                                if(this.props.get('gallery_id') > 0)
                                                    window.send_to_editor('[galleria gid="' + this.props.get('gallery_id') + '"]');
                                            }
                                        });

                                        this.states.add(new wp.media.controller.Galleriapress());

                                        this.on('content:render:galleriapress',
                                                function () 
                                                {
			                                              var view = new wp.media.view.Galleriapress(
                                                        {
				                                                    controller: this,
				                                                    model:      this.state().props
			                                                  }).render();
                                                    
			                                              this.content.set( view );

                                                    console.log("render"); 

                                                    this.$el.addClass('hide-router');
                                                },
                                                this);

                                        this.on('toolbar:create:galleriapress-insert',
                                                function(toolbar)
                                                {
                                                    toolbar.view = new wp.media.view.Toolbar.GalleriapressToolbar({ controller : this });
                                                },
                                                this);
                                        this.menu.render();

                                        this.on('content:activate:galleriapress',
                                                function()
                                                {
                                                },
                                                this);


                                    }, media);
                       });