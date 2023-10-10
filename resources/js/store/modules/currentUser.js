import axios from "axios";
import router from "../../router";

const state = {
    currentUser: {},
};

const getters = {
    currentUser(state) {
        return state.currentUser;
    },
};

const actions = {
    async fetchCurrentUser(context) {
        try {
            const response = await axios.get("/api/auth/init");
            context.commit("setCurrentUser", response.data.user);
        } catch (error) {
            console.log(error.request.status);
            if (error.request.status == "401") {
                localStorage.removeItem("access_token");
                router.push("/login");
            }
        }
    },
};

const mutations = {
    setCurrentUser(state, user) {
        state.currentUser = user;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};
