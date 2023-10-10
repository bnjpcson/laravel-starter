<template>
    <v-main>
        <v-app-bar dense dark app>
            <v-btn icon @click.stop="drawer = !drawer">
                <v-app-bar-nav-icon></v-app-bar-nav-icon>
            </v-btn>

            <v-spacer></v-spacer>

            <v-menu offset-y :nudge-width="200">
                <template v-slot:activator="{ on, attrs }">
                    <v-btn small rounded v-bind="attrs" v-on="on" color="grey darken-3">
                        <v-icon> mdi-menu-down </v-icon>
                    </v-btn>
                </template>
                <v-card color="grey lighten-3">
                    <v-card-text class="text-center">
                        <v-row>
                            <v-col><img src="https://cdn-icons-png.flaticon.com/512/147/147144.png" width="150" height="150"
                                    alt="User" style="border-radius: 50%" /></v-col>
                        </v-row>
                        <v-row>
                            <v-col>
                                <h5 class="text--secondary">
                                    {{ currentUser.name }}
                                </h5>
                                <h6 class="text--disabled">
                                    {{
                                        currentUser.id === 1
                                        ? "Administrator"
                                        : currentUser.position
                                            ? currentUser.position.name
                                            : ""
                                    }}
                                </h6>
                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-divider class="pa-0 mb-0"></v-divider>
                    <v-card-actions class="grey darken-2 d-flex justify-content-around">
                        <div>
                            <v-btn class="white--text" plain small @click="userProfile()">
                                <v-icon small class="mr-1">mdi-account</v-icon> Profile
                            </v-btn>
                        </div>
                        <div>
                            <v-btn @click="confirmLogout()" class="red--text text--accent-3 font-weight-bold" plain small>
                                <v-icon small class="mr-1">mdi-logout</v-icon> Logout
                            </v-btn>
                        </div>
                    </v-card-actions>
                </v-card>
            </v-menu>
        </v-app-bar>

        <!-- Sidebar -->
        <v-navigation-drawer v-model="drawer" dark app>
            <v-list>
                <v-list-item class="px-2">
                    <v-list-item-avatar class="rounded-5" height="60" width="60">
                        <v-img src="https://cdn-icons-png.flaticon.com/512/147/147144.png"></v-img>
                    </v-list-item-avatar>
                    <v-list-item-content>
                        <v-list-item-title>Laravel Socket.IO</v-list-item-title>
                        <v-list-item-subtitle>{{ currentUser.name }}</v-list-item-subtitle>
                    </v-list-item-content>
                </v-list-item>
            </v-list>

            <v-divider></v-divider>

            <v-list>
                <v-list-item link to="/">
                    <v-list-item-icon>
                        <v-icon>mdi-view-dashboard</v-icon>
                    </v-list-item-icon>
                    <v-list-item-title>Dashboard</v-list-item-title>
                </v-list-item>

                <v-list-item link :to="{ name: 'users.show' }"
                    v-if="hasPermission('user-list') || hasPermission('user-create') || hasPermission('user-edit') || hasPermission('user-delete')">
                    <v-list-item-icon>
                        <v-icon>mdi-account-multiple</v-icon>
                    </v-list-item-icon>
                    <v-list-item-title>Users</v-list-item-title>
                </v-list-item>

                <v-list-group no-action v-if="hasPermission('role-list') ||
                    hasPermission('role-create') ||
                    hasPermission('role-edit') ||
                    hasPermission('role-delete') ||
                    hasPermission('permission-list') ||
                    hasPermission('permission-create') ||
                    hasPermission('permission-edit') ||
                    hasPermission('permission-delete')
                    ">
                    <!-- List Group Icon-->
                    <v-icon slot="prependIcon">mdi-cog</v-icon>
                    <!-- List Group Title -->
                    <template v-slot:activator>
                        <v-list-item-content>
                            <v-list-item-title>Settings</v-list-item-title>
                        </v-list-item-content>
                    </template>
                    <!-- List Group Items -->
                    <v-list-item link :to="{ name: 'roles.show' }" v-if="hasPermission('role-list') ||
                        hasPermission('role-create') ||
                        hasPermission('role-edit') ||
                        hasPermission('role-delete')">
                        <v-list-item-content>
                            <v-list-item-title>Roles</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item link :to="{ name: 'permissions.show' }" v-if="hasPermission('permission-list') ||
                        hasPermission('permission-create') ||
                        hasPermission('permission-edit') ||
                        hasPermission('permission-delete')">
                        <v-list-item-content>
                            <v-list-item-title>Permissions</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </v-list-group>
            </v-list>
        </v-navigation-drawer>

        <v-overlay :absolute="absolute" :value="overlay">
            <v-progress-circular :size="70" :width="7" color="primary" indeterminate></v-progress-circular>
        </v-overlay>

        <div class="d-flex flex-column">
            <v-layout column justify-space-between class="vh-100">
                <router-view />
                <v-container fluid style="background-color: #272727;">
                    <v-row>
                        <v-col class="text-center text-white">
                            Copyright © {{ new Date().getFullYear() }} —
                            <strong> LARAVEL SOCKET.IO </strong>
                        </v-col>
                    </v-row>
                </v-container>
            </v-layout>
        </div>

        <!-- <v-footer padless dense dark app>
            <v-col class="text-center" cols="12">
                Copyright © {{ new Date().getFullYear() }} —
                <strong> LARAVEL SOCKET.IO </strong>
            </v-col>
        </v-footer> -->
    </v-main>
</template>

<script>
import axios from "axios";
import { mapState, mapActions, mapGetters } from "vuex";


export default {
    data() {
        return {
            absolute: true,
            overlay: true,
            drawer: true,
            user: {}
        };
    },

    methods: {
        getUser() {
            axios.get("/api/try").then(
                (response) => {
                    console.log(response.data);
                },
                (error) => {
                    console.log("Failed to fetch user: " + error);
                }
            );
        },
        websocket() {
            // Socket.IO fetch data
            this.$options.sockets.sendData = (data) => {
                let action = data.action;
                if (action == "users") {
                    this.fetchCurrentUser();
                }

                if (action == 'roles' || action == "permissions") {
                    this.fetchUserRolesPermissions();
                }
            };
        },
        logout() {
            this.overlay = true;
            axios.get("/api/auth/logout").then(
                (response) => {
                    if (response.data.success) {
                        this.overlay = false;

                        // remove all local storage including access_token
                        window.localStorage.clear();
                        this.$router.push("/login").catch(() => { });
                    }
                },
                (error) => {
                    this.overlay = false;
                    console.log(error);

                    // if unauthenticated (401)
                    if (error.response.status == "401") {

                        // remove all local storage including access_token
                        window.localStorage.clear();
                        this.$router.push({ name: "login" });
                    }
                }
            );
        },
        confirmLogout() {
            this.$swal({
                title: "Log Out",
                text: "Are you sure you want to log out?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "primary",
                cancelButtonColor: "secondary",
                confirmButtonText: "Log Out",
            }).then((result) => {

                if (result.value) {
                    this.overlay = true;
                    this.logout();
                }

            });
        },
        userProfile() {
            this.$router.push({ name: "users.profile" }).catch(function () { });
        },
        ...mapActions("currentUser", ['fetchCurrentUser']),
        ...mapActions("rolespermissions", ['fetchUserRolesPermissions'])
    },
    computed: {
        currentUser() {
            return this.$store.getters.currentUser;
        },
        ...mapGetters("currentUser", ['currentUser']),
        ...mapGetters("rolespermissions", ["hasRole", "hasPermission"]),
    },
    watch: {

    },
    mounted() {
        // this.getUser();
        this.fetchCurrentUser();
        this.fetchUserRolesPermissions();
        this.websocket()

        this.$nextTick(function () {
            this.overlay = false;
        })
    },
};

</script>