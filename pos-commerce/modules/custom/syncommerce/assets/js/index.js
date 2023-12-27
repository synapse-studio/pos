/**
 * @file
 * Vue-cart index.js.
 */

var apps = [];

const delimiters = ["${", "}"];
const comments = true;

window.dataLayer = window.dataLayer || [];
function gtag() {
  dataLayer.push(arguments);
}

const store = Vuex.createStore({
  state: {
    cart: {
      items: {},
      quantity: 0,
      total: 0,
    },
    favorite: {
      total: 0,
    },
    color: 0,
  },
  mutations: {
    propertiesSet(state, properties) {
      for (property in properties) {
        state[property] = properties[property];
      }
    },
    updateCart(state, cart) {
      state.cart = cart;
    },
    updateColor(state, color) {
      state.color = color;
    },
    updateFavorite(state, fav) {
      state.favorite = fav;
    },
  },
  getters: {
    cartIsEmpty: (state) => {
      if (!Array.isArray(state.cart.items)) {
        return true;
      }
      return !state.cart.items.length;
    },
    cart: (state) => {
      return state.cart;
    },
    vid: (state) => {
      if (state.vid) {
        return +state.vid;
      }
      return null;
    },
    selectedProducts: (state) => {
      if (state.selected_products) {
        return state.selected_products;
      }
      return [];
    },
  },
  actions: {
    initCart({ commit }) {
      return new Promise((resolve) => {
        axios.get("/api/cart-load").then((response) => {
          commit("updateCart", response.data);
          resolve(response);
        });
      });
    },
    sendDataLayers(state, carts) {
      let old_cart = carts.old_cart;
      let new_cart = carts.new_cart;
      if (old_cart.items && new_cart.items) {
        let old_cart_items = {};
        let new_cart_items = {};
        let items_old = Object.values(old_cart.items);
        items_old.forEach((element) => {
          old_cart_items[element.vid] = element;
        });
        let items_new = Object.values(new_cart.items);
        items_new.forEach((element) => {
          new_cart_items[element.vid] = element;
        });
        let new_cart_items_length = Object.keys(new_cart_items).length;
        let old_cart_items_length = Object.keys(old_cart_items).length;
        if (new_cart_items_length == old_cart_items_length) {
          for (const [key, element] of Object.entries(new_cart_items)) {
            let old_quantity = +old_cart_items[key].quantity;
            if (old_quantity != +element.quantity) {
              let change_quantity = element.quantity - old_quantity;
              if (change_quantity > 0) {
                dataLayerEvent("add", element, Math.abs(change_quantity));
              }
              if (change_quantity < 0) {
                dataLayerEvent("remove", element, Math.abs(change_quantity));
              }
            }
          }
        } else if (new_cart_items_length > old_cart_items_length) {
          for (const [key, element] of Object.entries(new_cart_items)) {
            if (old_cart_items[key] == undefined) {
              dataLayerEvent("add", element, element.quantity);
            }
          }
        } else if (new_cart_items_length < old_cart_items_length) {
          for (const [key, element] of Object.entries(old_cart_items)) {
            if (new_cart_items[key] == undefined) {
              dataLayerEvent("remove", element, element.quantity);
            }
          }
        }
      }
    },
    refreshStock({ commit, getters }) {
      if (getters.cartIsEmpty) {
        return;
      }
      let items = {};
      let cart = getters.cart;

      cart.items.forEach((item) => {
        let vid = item.vid;
        let quantity = item.quantity;
        items[item.id] = { vid, quantity };
      });
      return new Promise((resolve) => {
        axios.post("/cart/refresh-stock", { items }).then((response) => {
          response.data.forEach((item_stock_data) => {
            let item = cart.items.find(
              (item) => item.vid == item_stock_data.vid
            );
            if (item) {
              item.stock = item_stock_data.stock;
            }
          });
          commit("updateCart", cart);
          resolve(response);
        });
      });
    },
    addToCart({ commit, getters }, payload) {
      payload.selected_products = getters.selectedProducts;
      return new Promise((resolve) => {
        axios.post("/cart/add-item", payload).then((response) => {
          commit("updateCart", response.data);
          resolve(response);
        });
      });
    },
    setVariationQuantity({ commit, getters }, data) {
      let item_id = data.item_id;
      let quantity = data.quantity;
      let selected_products = getters.selectedProducts;
      let vid = getters.vid;
      let payload = {
        item_id,
        vid,
        quantity,
        selected_products,
      };
      return new Promise((resolve) => {
        axios.post("/cart/set-variation-quantity", payload).then((response) => {
          commit("updateCart", response.data);
          resolve(response);
        });
      });
    },
    setItemQuantity({ commit, getters }, data) {
      let item_id = data.item_id;
      let quantity = data.quantity;
      let selected_products = getters.selectedProducts;
      let vid = getters.vid;
      let payload = {
        item_id,
        vid,
        quantity,
        selected_products,
      };
      return new Promise((resolve) => {
        axios
          .post("/cart/set-order-item-quantity", payload)
          .then((response) => {
            commit("updateCart", response.data);
            resolve(response);
          });
      });
    },
    setItemNote(item) {
      axios
        .post("/cart/set-item-note", {
          itemId: item.id,
          note: item.data_note,
        })
        .then((response) => {
          this.commit("updateCart", response.data);
        });
    },
    removeItem({ getters }, data) {
      let item_id = data.item_id;
      let selected_products = getters.selectedProducts;
      let vid = getters.vid;
      axios.post("/cart/remove-cart-item", { item_id, vid, selected_products });
    },
  },
});
