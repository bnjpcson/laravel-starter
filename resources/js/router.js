import Vue from "vue";
import VueRouter from "vue-router";

import Home from "./views/Home.vue";

import PageNotFound from "./views/components/PageNotFound.vue";
import ServerError from "./views/components/ServerError.vue";
import Unauthorize from "./views/components/Unauthorize.vue";

import Login from "./views/auth/Login.vue";

import Dashboard from "./views/dashboard/Dashboard.vue";

import UserShow from "./views/user/UserShow.vue";
import UserProfile from "./views/user/UserProfile.vue";

import Roles from "./views/settings/Roles.vue";
import Permissions from "./views/settings/Permissions.vue";

Vue.use(VueRouter);

const routes = [
    {
        path: "/",
        component: Home,
        children: [
            {
                path: "/",
                component: Dashboard,
                name: "dashboard.show",
            },
            {
                path: "/users",
                component: UserShow,
                name: "users.show",
            },
            {
                path: "/users/profile",
                component: UserProfile,
                name: "users.profile",
            },
            {
                path: "/roles",
                component: Roles,
                name: "roles.show",
            },
            {
                path: "/permissions",
                component: Permissions,
                name: "permissions.show",
            },
            {
                path: "/unauthorize",
                name: "unauthorize",
                component: Unauthorize,
            },
            {
                path: "/pagenotfound",
                name: "pagenotfound",
                component: PageNotFound,
            },
            {
                path: "/servererror",
                name: "servererror",
                component: ServerError,
            },
        ],
        beforeEnter(to, from, next) {
            if (localStorage.getItem("access_token")) {
                next();
            } else {
                next("/login");
            }
        },
    },
    {
        path: "/login",
        name: "login.show",
        component: Login,
        beforeEnter(to, from, next) {
            if (localStorage.getItem("access_token")) {
                next("/");
            } else {
                next();
            }
        },
    },
    {
        path: "*",
        component: PageNotFound,
    },
];

const router = new VueRouter({
    routes,
});

export default router;
