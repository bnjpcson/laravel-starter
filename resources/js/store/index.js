import Vue from "vue";
import Vuex from "vuex";

import currentUser from "./modules/currentUser";
import rolespermissions from "./modules/rolespermissions";

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        currentUser,
        rolespermissions,
    },
});
