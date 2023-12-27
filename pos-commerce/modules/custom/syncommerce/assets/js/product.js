
(function ($) {
  "use strict";

  if (document.readyState != "loading") {
    vueCartApp("#block-adaptive-content");
  } else {
    window.addEventListener("DOMContentLoaded", function () {
      vueCartApp("#block-adaptive-content");
    });
  }
  function vueCartApp(sel) {
    var app;
    const elements = document.querySelectorAll(sel);
    const each = Array.prototype.forEach;
    each.call(elements, function (el, i) {
      let comp_template = el.querySelector("product");
      app = Vue.createApp({ delimiters, comments, el })
        .use(store)
        .use($cookies);
      app.component("product", {
        template: comp_template,
        delimiters: delimiters,
        data() {
          return {
            ready: false,
            products: [],
            catalog: [],
            attributes: [],
            filters: {
              article: '',
              name: '',
              status: true,
              catalog: '',
              max: 100,
              page: 1,
            },
          };
        },
        computed: {
          cart() {
            return this.$store.state.cart;
          },
        },
        methods: {
          initCart() {
            let loadPath = "/syncommerce/products_list";
            axios
              .post(loadPath, {
                filters: this.filters,
              })
              .then((response) => {
                if (response.data.quantity > 0) {
                  this.ready = true;
                }
                this.products = response.data.products;
                this.catalog = response.data.catalog;
                this.attributes = response.data.attributes;
              });
          },
          toggleVariations(product) {
            product.viewVariation = !product.viewVariation;
          },
          filterApply() {
            this.initCart();
          },
          saveProduct(id) {
            axios.post("/syncommerce/updateProduct", {
              id: id,
              article: this.products[id].article,
              title: this.products[id].title,
              status: this.products[id].status,
              catalog: this.products[id].catalog,
            }).then(
              (response) => {
                this.products[id].info = response.data;
                setTimeout(() => {
                  this.products[id].info = '';
                }, 3000);
              },
            );
          },
          saveVariation(productId, variationId) {
            let attributes = new Object;
            for (let attribute in this.products[productId].variations[variationId].attributes) {
              attributes[attribute] = this.products[productId].variations[variationId].attributes[attribute].id;
            }
            axios.post("/syncommerce/updateVariation", {
              id: variationId,
              price_number: this.products[productId].variations[variationId].price_number,
              old_price: this.products[productId].variations[variationId].old_price,
              stock: this.products[productId].variations[variationId].stock,
              status: this.products[productId].variations[variationId].status,
              attributes: attributes,
            }).then(
              (response) => {
                this.products[productId].variations[variationId].info = response.data;
                setTimeout(() => {
                  this.products[productId].variations[variationId].info = '';
                }, 3000);
              },
            );
          },
          numberFormat(number, step, decimals = 2, dec_point = ".", thousands_sep = " ") {
            let s_number =
              Math.abs(parseInt((number = (+number || 0).toFixed(decimals)))) + "";
            let len = s_number.length;
            let tchunk = len > 3 ? len % 3 : 0;
            let ch_first = tchunk ? s_number.substr(0, tchunk) + thousands_sep : "";
            let ch_rest = s_number
              .substr(tchunk)
              .replace(/(\d\d\d)(?=\d)/g, "$1" + thousands_sep);
            let ch_last = decimals
              ? dec_point + (Math.abs(number) - s_number).toFixed(decimals).slice(2)
              : "";
            if (step == 1) {
              ch_last = '';
            }
            return ch_first + ch_rest + ch_last;
          },
          isEmpty(value) {
            if (!!value && value instanceof Array) {
              return value.length < 1;
            }
            if (!!value && typeof value === "object") {
              for (var key in value) {
                if (hasOwnProperty.call(value, key)) {
                  return false;
                }
              }
            }
            return !value;
          },
          readQuantity(e, item) {
            let quantity = e.target.innerText;
            quantity = parseInt(quantity.replaceAll(" ", ""));
            if (quantity > 999) {
              quantity = 999;
            } else if (quantity < 1) {
              quantity = 1;
            }
            this.changeQuantity(item.id, quantity, item.qstep);
          },
        },
        created() {
          this.initCart();
        },
      });
      app.mount(el);
    });
  }

  String.prototype.replaceAll = function (search, replacement) {
    return this.split(search).join(replacement);
  };
})(jQuery);
