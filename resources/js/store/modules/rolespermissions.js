import axios from "axios";
import router from "../../router";

const state = {
    roles: [],
    permissions: [],
};

const getters = {
    hasRole:
        (state) =>
        (...role) => {
            return role.every((value) => state.roles.includes(value));
        },

    hasAnyRole:
        (state) =>
        (...role) => {
            return role.some((value) => state.roles.includes(value));
        },

    hasPermission:
        (state) =>
        (...permission) => {
            return permission.every((value) =>
                state.permissions.includes(value)
            );
        },

    hasAnyPermission:
        (state) =>
        (...permission) => {
            return permission.some((value) =>
                state.permissions.includes(value)
            );
        },
};

const actions = {
    async fetchUserRolesPermissions(context) {
        try {
            const response = await axios.get("/api/user/rolespermissions");
            context.commit("setUserRoles", response.data.user_roles);
            context.commit(
                "setUserPermissions",
                response.data.user_permissions
            );
        } catch (error) {
            console.log(error.request.status);
            if (error.request.status == "401") {
                router.push({ name: "unauthorize" });
            }
        }
    },
};

const mutations = {
    setUserRoles(state, roles) {
        state.roles = roles;
    },
    setUserPermissions(state, permissions) {
        state.permissions = permissions;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};
