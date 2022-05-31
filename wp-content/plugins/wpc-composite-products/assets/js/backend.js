'use strict';

(function($) {
  $(function() {
    wooco_active_options();
    wooco_active_settings();
    wooco_option_none_image();
    wooco_active_type();
    wooco_arrange();

    setInterval(function() {
      $('.wooco-product-search').each(function() {
        var _val = $(this).val();
        if (Array.isArray(_val)) {
          $(this).
              closest('div').
              find('.wooco-product-search-input').
              val(_val.join());
        } else {
          $(this).
              closest('div').
              find('.wooco-product-search-input').
              val(String(_val));
        }
      });
    }, 1000);
  });

  // choose background image
  var wooco_file_frame;

  $(document).
      on('click touch', '#wooco_option_none_image_upload', function(event) {
        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (wooco_file_frame) {
          // Open frame
          wooco_file_frame.open();
          return;
        }

        // Create the media frame.
        wooco_file_frame = wp.media.frames.wooco_file_frame = wp.media({
          title: 'Select a image to upload', button: {
            text: 'Use this image',
          }, multiple: false,	// Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        wooco_file_frame.on('select', function() {
          // We set multiple to false so only get one image from the uploader
          var attachment = wooco_file_frame.state().
              get('selection').
              first().
              toJSON();

          // Do something with attachment.id and/or attachment.url here
          if ($('#wooco_option_none_image_preview img').length) {
            $('#wooco_option_none_image_preview img').
                attr('src', attachment.url);
          } else {
            $('#wooco_option_none_image_preview').
                html('<img src="' + attachment.url + '"/>');
          }
          $('#wooco_option_none_image_id').val(attachment.id);
        });

        // Finally, open the modal
        wooco_file_frame.open();
      });

  $(document).
      on('change', 'select[name="_wooco_option_none_image"]', function() {
        wooco_option_none_image();
      });

  $(document).on('change', 'select[name="_wooco_change_price"]', function() {
    wooco_active_options();
  });

  $(document).on('change', '#product-type', function() {
    wooco_active_settings();
  });

  // product search
  $(document).on('change', '.wooco-product-search', function() {
    var _val = $(this).val();
    if (Array.isArray(_val)) {
      $(this).
          closest('div').
          find('.wooco-product-search-input').
          val(_val.join());
    } else {
      $(this).
          closest('div').
          find('.wooco-product-search-input').
          val(String(_val));
    }
  });

  // category search
  $(document).on('change', '.wooco-category-search', function() {
    var _val = $(this).val();
    if (Array.isArray(_val)) {
      $(this).
          closest('div').
          find('.wooco-category-search-input').
          val(_val.join());
    } else {
      $(this).
          closest('div').
          find('.wooco-category-search-input').
          val(String(_val));
    }
  });

  $(document).on('click touch', '.wooco_expand_all', function(e) {
    e.preventDefault();

    $('.wooco_component_inner').addClass('active');
  });

  $(document).on('click touch', '.wooco_collapse_all', function(e) {
    e.preventDefault();

    $('.wooco_component_inner').removeClass('active');
  });

  $(document).on('click touch', '.wooco_add_component', function(e) {
    e.preventDefault();
    $('.wooco_components').addClass('wooco_components_loading');

    var count = $('.wooco_component').length;
    var data = {
      action: 'wooco_add_component', count: count,
    };

    $.post(ajaxurl, data, function(response) {
      $('.wooco_components tbody').append(response);
      wooco_active_type();
      wooco_arrange();
      $('.wooco_components').removeClass('wooco_components_loading');
    });
  });

  $(document).on('click touch', '.wooco_duplicate_component', function(e) {
    e.preventDefault();
    $('.wooco_components').addClass('wooco_components_loading');

    var $component = $(this).closest('.wooco_component');
    var count = $('.wooco_component').length;
    var data = {
      action: 'wooco_add_component', component: {
        name: $component.find('.wooco_component_name_val').val(),
        desc: $component.find('.wooco_component_desc_val').val(),
        type: $component.find('.wooco_component_type_val').val(),
        categories: $component.find('.wooco_component_categories_val').val(),
        orderby: $component.find('.wooco_component_orderby_val').val(),
        order: $component.find('.wooco_component_order_val').val(),
        products: $component.find('.wooco_component_products_val').val(),
        tags: $component.find('.wooco_component_tags_val').val(),
        other: $component.find('.wooco_component_other_val').val(),
        default: $component.find('.wooco_component_default_val').val(),
        exclude: $component.find('.wooco_component_exclude_val').val(),
        optional: $component.find('.wooco_component_optional_val').val(),
        price: $component.find('.wooco_component_price_val').val(),
        qty: $component.find('.wooco_component_qty_val').val(),
        custom_qty: $component.find('.wooco_component_custom_qty_val').val(),
        min: $component.find('.wooco_component_min_val').val(),
        max: $component.find('.wooco_component_max_val').val(),
      }, count: count,
    };

    $.post(ajaxurl, data, function(response) {
      $('.wooco_components tbody').append(response);
      wooco_active_type();
      wooco_arrange();
      $('.wooco_components').removeClass('wooco_components_loading');
    });
  });

  $(document).on('click touch', '.wooco_save_components', function(e) {
    e.preventDefault();

    var $this = $(this);

    $this.addClass('wooco_disabled');
    $('.wooco_components').addClass('wooco_components_loading');

    var form_data = $('#wooco_settings').
        find('input, select, button, textarea').
        serialize() || 0;
    var data = {
      action: 'wooco_save_components',
      pid: $('#post_ID').val(),
      form_data: form_data,
    };

    $.post(ajaxurl, data, function(response) {
      $('.wooco_components').removeClass('wooco_components_loading');
      $this.removeClass('wooco_disabled');
    });
  });

  $(document).on('click touch', '.wooco_remove_component', function(e) {
    e.preventDefault();

    if (confirm('Are you sure?')) {
      $(this).closest('.wooco_component').remove();
    }
  });

  $(document).on('click touch', '.wooco_component_heading', function(e) {
    if (($(e.target).closest('.wooco_duplicate_component').length === 0) &&
        ($(e.target).closest('.wooco_remove_component').length === 0)) {
      $(this).closest('.wooco_component_inner').toggleClass('active');
    }
  });

  $(document).on('change, keyup', '.wooco_component_name_val', function() {
    var _val = $(this).val();
    $(this).
        closest('.wooco_component_inner').
        find('.wooco_component_name').
        html(_val);
  });

  $(document).on('click touch', '.wooco-product-types-btn', function(e) {
    $('#wooco_product_types').
        dialog({
          minWidth: 460,
          modal: true,
          dialogClass: 'wpc-dialog',
          open: function() {
            $('.ui-widget-overlay').bind('click', function() {
              $('#wooco_product_types').dialog('close');
            });
          },
        });
  });

  $(document).on('change', '.wooco_component_type', function() {
    wooco_active_type_component($(this));
  });

  function wooco_active_type() {
    $('.wooco_component_type').each(function() {
      wooco_active_type_component($(this));
    });
  }

  function wooco_active_type_component($this) {
    var _val = $this.val();
    var _text = $this.find(':selected').text().trim();

    $this.closest('.wooco_component').find('.wooco_hide').hide();
    $this.closest('.wooco_component').
        find('.wooco_component_type_label').
        text(_text);

    if (_val !== '') {
      if (_val === 'products' || _val === 'categories' || _val === 'tags') {
        $this.closest('.wooco_component').
            find('.wooco_show_if_' + _val).
            show().
            css('display', 'flex');
      } else {
        $this.closest('.wooco_component').
            find('.wooco_show_if_other').
            show().
            css('display', 'flex');
      }
    }

    if (_val === 'types') {
      $this.closest('.wooco_component').
          find('.wooco-product-types-btn').
          show();
    } else {
      $this.closest('.wooco_component').
          find('.wooco-product-types-btn').
          hide();
    }

    $this.closest('.wooco_component').find('.wooco_show').show();
    $this.closest('.wooco_component').find('.wooco_hide_if_' + _val).hide();
  }

  function wooco_arrange() {
    $('.wooco_components tbody').sortable({
      handle: '.wooco_move_component',
    });
  }

  function wooco_option_none_image() {
    if ($('select[name="_wooco_option_none_image"]').val() === 'custom') {
      $('.wooco_option_none_image_custom').show();
    } else {
      $('.wooco_option_none_image_custom').hide();
    }
  }

  function wooco_active_options() {
    if ($('select[name="_wooco_change_price"]').val() === 'yes_custom') {
      $('input[name="_wooco_change_price_custom"]').show();
    } else {
      $('input[name="_wooco_change_price_custom"]').hide();
    }
  }

  function wooco_active_settings() {
    if ($('#product-type').val() === 'composite') {
      $('li.general_tab').addClass('show_if_composite');
      $('#general_product_data .pricing').addClass('show_if_composite');
      $('.composite_tab').addClass('active');
      $('#_downloadable').
          closest('label').
          addClass('show_if_composite').
          removeClass('show_if_simple');
      $('#_virtual').
          closest('label').
          addClass('show_if_composite').
          removeClass('show_if_simple');
      $('.show_if_external').hide();
      $('.show_if_simple').show();
      $('.show_if_composite').show();
      $('.product_data_tabs li').removeClass('active');
      $('.panel-wrap .panel').hide();
      $('#wooco_settings').show();
    } else {
      $('li.general_tab').removeClass('show_if_composite');
      $('#general_product_data .pricing').removeClass('show_if_composite');
      $('#_downloadable').
          closest('label').
          removeClass('show_if_composite').
          addClass('show_if_simple');
      $('#_virtual').
          closest('label').
          removeClass('show_if_composite').
          addClass('show_if_simple');
      $('.show_if_composite').hide();
      $('.show_if_' + $('#product-type').val()).show();
    }
  }
})(jQuery);