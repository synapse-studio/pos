/**
 * @file
 * Author: Synapse-studio.
 */

(function ($) {
  "use strict";

  const DRAGTHRESHOLD = window.innerWidth > 1024 ? 20 : 0;

  $(document).ready(function () {
    serviceBlockSlider();
    moduleSlider();
  });

  function serviceBlockSlider() {
    var $slider = $(".service-block-slider .field__items");
    let find = $slider.find('.field__item');
    if ($slider.length && find.length > 1) {
      $slider.flickity({
        dragThreshold: DRAGTHRESHOLD,
        cellAlign: "center",
        initialIndex: 1,
        pageDots: true,
        wrapAround: true,
        imagesLoaded: true,
        arrowShape: "M 20,50 L 55,80 L 55,70 L 30,50  L 55,30 L 55,20 Z",
      });
      $slider.on("dragStart.flickity", function (event, pointer) {
        $(this).addClass("is-dragging");
      });
      $slider.on("dragEnd.flickity", function (event, pointer) {
        $(this).removeClass("is-dragging");
      });
    }
  }

  function moduleSlider() {
    var $slider = $(".flickity");
    if ($slider.length) {
      $slider.flickity({
        dragThreshold: DRAGTHRESHOLD,
        cellAlign: "left",
        autoPlay: 4000,
        pageDots: false,
        wrapAround: true,
        imagesLoaded: true,
      });
      $slider.on("dragStart.flickity", function (event, pointer) {
        $(this).addClass("is-dragging");
      });
      $slider.on("dragEnd.flickity", function (event, pointer) {
        $(this).removeClass("is-dragging");
      });
      let slideContent = $slider.find(".service-promo-content");
      let maxHeight = 0;
      slideContent.each(function () {
        let slideContentHeight = $(this).outerHeight();
        if (slideContentHeight > maxHeight) {
          maxHeight = slideContentHeight;
        }
      });
      slideContent.each(function () {
        $(this).attr("style", "min-height: " + maxHeight + "px;");
      });
    }
  }

})(this.jQuery);
