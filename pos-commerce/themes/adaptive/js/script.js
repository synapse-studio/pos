/**
 * @file
 * Author: Synapse-studio.
 */

(function ($) {
  'use strict';

  const DRAGTHRESHOLD = window.innerWidth > 1024 ? 20 : 0;

  $(document).ready(function () {
    siteReady();
    calcWorkScopeItemsHeight();
    setTimeout(() => {
      formControlBehaviour();
    }, 10);
  });

  function calcWorkScopeItemsHeight() {
    setTimeout(() => {
      let workScopItems = $(".work-scope-items");
      workScopItems.each(function (index) {
        calcHeight($(this));
      });
    }, 300);
  }

  function calcHeight($container) {
    if ($container.length) {
      let pictures = $container.find(".work-scope-item");
      var heights = [];
      let i = 0;
      pictures.each(function () {
        heights[i] = $(this).height();
        i++;
      });
      let maxHeight = -1;
      heights.forEach((element) => {
        if (element > maxHeight) {
          maxHeight = element;
        }
      });
      pictures.each(function () {
        $(this).css("height", maxHeight);
      });
    }
  }
  

  function phoneInputMask($field) {
    if (drupalSettings.path.currentLanguage == 'ru') {
      $field.inputmask("+7 (999) 999-99-99", {
        "clearIncomplete": true,
        "showMaskOnHover": false,
      });
    }
    else {
      $field.inputmask("+9 (999) 999-99-99", {
        "clearIncomplete": true,
        "showMaskOnHover": false,
      });
    }
  }

  function formControlBehaviour() {
    let forms = $('.checkout-form, .contact-message-form');
    if (forms.length) {
      let formControls = forms.find('input, textarea');
      if (formControls.length) {
        formControls.each(function () {
          let controlWrapper = $(this).closest('.form-item:not(.form-type-select):not(.form-type-managed-file)');
          if (
            !$(this).attr("placeholder") &&
            ($(this).attr("value") == "" || (($(this)[0].localName == "textarea" || $(this).attr("type") == 'password') && $(this)[0].innerHTML == ""))
            ) {
            if ((controlWrapper.length) && (($(this).val().length == 0))) {
              controlWrapper.addClass('form-item--empty');
            }
          }
          $(this).on('change', function () {
            if ($(this).val().length > 0) {
              controlWrapper.removeClass('form-item--empty');
            } else {
              controlWrapper.addClass('form-item--empty');
            }
          });
        });
      }
    }
  }

  function siteReady() {
    const END = Date.now() + 3000;
    var body = document.body;

    var interval = setInterval(() => {
      if (body.classList.contains('site-is-ready')) {
        clearInterval(interval);
        return;
      }
      if (Date.now() > END) {
        body.classList.add('site-is-ready');
        clearInterval(interval);
      }
    }, 1000);

    window.onload = () => {
      body.classList.add('site-is-ready');
    }
  }

  if (typeof (Drupal) === 'object') {
    Drupal.behaviors.adaptive = {
      attach: function (context) {
        let $field_contact = $(context).find('.field--name-field-contact input');
        let $field_phone = $(context).find('.field--name-field-phone input');
        if ($field_contact.length) {
          phoneInputMask($field_contact);
        }
        if ($field_phone.length) {
          phoneInputMask($field_phone);
        }
      }
    };
  }

})(this.jQuery);
