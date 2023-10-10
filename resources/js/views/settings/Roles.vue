<template>
    <v-container class="px-lg-10">
        <v-row>
            <v-col>
                <BreadCrumbComponent :items="items"></BreadCrumbComponent>
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <DataTableComponent :parentName="'Roles List'" :getPath="path" :headers="headers" :toggleAdd="toggleDialog"
                    :addPermission="'role-create'" ref="DataTableComponent">

                    <template v-slot:item.actions="{ item }">
                        <v-icon small color="blue" v-if="item.id == 1 && hasPermission('role-edit')"
                            @click="toggleDialog(item.id)">
                            mdi-eye
                        </v-icon>
                        <v-icon small color="green" v-if="item.id != 1 && hasPermission('role-edit')"
                            @click="toggleDialog(item.id)">
                            mdi-pencil
                        </v-icon>
                        <v-icon small color="red" v-if="item.id != 1 && hasPermission('role-delete')"
                            @click="showConfirmAlert(item.id)">
                            mdi-delete
                        </v-icon>
                    </template>

                    <template v-slot:item.permission="{ item }">
                        <v-chip small color="secondary" v-if="item.permissions[0] != null"
                            @click="togglePermissionDialog(item.id)">
                            {{ item.permissions[0].name }}
                        </v-chip>
                        <v-chip small v-if="item.permissions.length > 1" @click="togglePermissionDialog(item.id)">
                            {{ "+" }}
                            {{
                                item.permissions.length > 1
                                ? item.permissions.length - 1
                                : item.permissions[0].name
                            }}
                            {{ "others" }}
                        </v-chip>
                    </template>
                </DataTableComponent>
            </v-col>
        </v-row>
        <v-dialog v-model="dialog" persistent scrollable eager max-width="500px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ formTitle }}</span>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col class="mt-0 mb-0 pt-0 pb-0">
                                <v-text-field label="Role" v-model="formData.role"
                                    :error-messages="roleErrors + formDataError.role"
                                    @input="$v.formData.role.$touch() + (formDataError.role = [])"
                                    @blur="$v.formData.role.$touch()" :disabled="buttonProps.disableSave"></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col class="mt-0 mb-0 pt-0 pb-0">
                                <v-autocomplete v-model="formData.permission" :items="permissions" item-text="name"
                                    item-value="name" label="Permissions" multiple chips
                                    :disabled="buttonProps.disableSave">
                                    <template v-slot:selection="data">
                                        <v-chip color="secondary" v-bind="data.attrs" :input-value="data.selected"
                                            @click="data.select">
                                            {{ data.item }}
                                        </v-chip>
                                    </template>
                                </v-autocomplete>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>

                <v-divider></v-divider>

                <v-card-actions class="d-flex justify-end pb-5">
                    <v-btn color="#E0E0E0" @click="btnCancel" :disabled="buttonProps.disableCancel">
                        Cancel
                    </v-btn>
                    <v-btn color="primary" @click="btnSave" :disabled="buttonProps.disableSave"
                        :loading="buttonProps.loadingSave" v-if="hasPermission('role-edit')">
                        Save
                    </v-btn>
                </v-card-actions>

            </v-card>
        </v-dialog>

        <v-dialog v-model="permissionsData.dialog" max-width="500px" eager>
            <v-card>
                <v-card-title>
                    <span>Permissions</span>
                    <v-spacer></v-spacer>
                    <v-btn class="ma-2" text icon @click="permissionsData.dialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text>
                    <v-chip-group column>
                        <v-chip color="secondary" v-for="item in permissionsData.permission" :key="item.id">
                            {{ item }}
                        </v-chip>
                    </v-chip-group>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-container>
</template>




<script>

import axios from "axios";
import { validationMixin } from "vuelidate";
import { required } from "vuelidate/lib/validators";
import { mapState, mapActions, mapGetters } from "vuex";


import BreadCrumbComponent from "../components/BreadCrumbComponent.vue";
import DataTableComponent from "../components/DataTableComponent.vue";


export default {
    mixins: [validationMixin],

    validations: {
        formData: {
            role: { required },
        },
    },

    components: {
        BreadCrumbComponent,
        DataTableComponent
    },

    data() {
        return {
            isLoading: true,
            items: [
                {
                    text: "DASHBOARD",
                    disabled: false,
                    href: "#/",
                },
                {
                    text: "ROLES",
                    disabled: true,
                },
            ],
            path: "api/roles/getroles",
            headers: [
                {
                    text: 'Role',
                    value: 'name',
                },
                {
                    text: 'Permissions',
                    value: 'permission',
                },
                {
                    text: 'Actions',
                    value: 'actions',
                    sortable: false,
                    width: "80px"
                },
            ],
            dialog: false,
            formData: {
                id: 0,
                role: [],
                permission: [],
            },
            editMode: false,
            formDataError: {
                role: [],
                permission: [],
            },
            buttonProps: {
                disableSave: false,
                loadingSave: false,
                disableCancel: false,
            },
            permissions: [],
            permissionsData: {
                dialog: false,
                items: [],
                permission: []
            }
        };
    },

    watch: {

    },

    methods: {
        async toggleDialog(id) {
            if (id == 1) {
                this.buttonProps.disableSave = true;
            } else {
                this.buttonProps.disableSave = false;
            }

            this.clear();
            if (id != undefined) {
                try {
                    let response = await axios.get('api/roles/selectroles', { params: { id: id } });

                    this.formData.role = response.data.role.name;
                    this.formData.id = response.data.role.id;
                    let permissions = [];
                    response.data.role.permissions.forEach(element => {
                        permissions.push(element.name);
                    });

                    this.formData.permission = permissions;
                    this.permissionsData.permission = permissions;
                    this.permissionsData.items = permissions;


                    this.editMode = true;
                    this.dialog = true;

                } catch (error) {
                    let message = error.response.data.message;
                    let statusText = error.response.statusText;
                    let status = error.response.status;

                    if (status == 403) {
                        this.$router.push({ name: "unauthorize" });
                    }
                    if (status == 404) {
                        this.$router.push({ name: "pagenotfound" });
                    }
                    if (status == 500) {
                        this.$router.push({ name: "servererror" });
                    }

                    this.$swal({
                        position: "center",
                        icon: "error",
                        title: statusText != "" ? statusText : "Error",
                        title: message != "" ? message : "Invalid Request",
                        showConfirmButton: false,
                        timer: 2500,
                    });
                }

            } else {
                this.editMode = false;
                this.dialog = true;
            }

        },

        async togglePermissionDialog(id) {
            try {
                let response = await axios.get('api/roles/selectroles', { params: { id: id } });

                let permissions = [];
                response.data.role.permissions.forEach(element => {
                    permissions.push(element.name);
                });

                this.permissionsData.permission = permissions;
                this.permissionsData.items = permissions;
                this.permissionsData.dialog = true;
            } catch (error) {
                let message = error.response.data.message;
                let statusText = error.response.statusText;
                let status = error.response.status;

                if (status == 403) {
                    this.$router.push({ name: "unauthorize" });
                }
                if (status == 404) {
                    this.$router.push({ name: "pagenotfound" });
                }
                if (status == 500) {
                    this.$router.push({ name: "servererror" });
                }

                this.$swal({
                    position: "center",
                    icon: "error",
                    title: statusText != "" ? statusText : "Error",
                    title: message != "" ? message : "Invalid Request",
                    showConfirmButton: false,
                    timer: 2500,
                });
            }
        },

        async btnSave() {

            this.$v.$touch();
            this.formDataError = {
                role: [],
                permission: []
            };

            if (!this.$v.$error) {
                this.buttonProps.disableSave = true;
                this.buttonProps.disableCancel = true;
                this.buttonProps.loadingSave = true;

                const data = this.formData;

                try {
                    let request = 'api/roles/createroles';
                    if (this.editMode) { //Edit
                        request = 'api/roles/updateroles';
                    }

                    let response = await axios.post(request, data);


                    if (!response.data.success) {
                        let errors = response.data.error;
                        let errorNames = Object.keys(response.data.error);

                        errorNames.forEach(value => {
                            this.formDataError[value].push(errors[value]);
                        });

                    } else {
                        this.$swal({
                            position: "center",
                            icon: "success",
                            title: "Record has been saved",
                            showConfirmButton: false,
                            timer: 2500,
                        });
                        this.dialog = false;
                        this.$refs.DataTableComponent.getItems();
                        this.$socket.emit("sendData", { action: "roles" });
                    }

                } catch (error) {
                    let message = error.response.data.message;
                    let statusText = error.response.statusText;
                    let status = error.response.status;

                    if (status == 403) {
                        this.$router.push({ name: "unauthorize" });
                    }
                    if (status == 404) {
                        this.$router.push({ name: "pagenotfound" });
                    }
                    if (status == 500) {
                        this.$router.push({ name: "servererror" });
                    }

                    this.$swal({
                        position: "center",
                        icon: "error",
                        title: statusText != "" ? statusText : "Error",
                        title: message != "" ? message : "Invalid Request",
                        showConfirmButton: false,
                        timer: 2500,
                    });
                } finally {
                    this.buttonProps.disableSave = false;
                    this.buttonProps.disableCancel = false;
                    this.buttonProps.loadingSave = false;
                }
            }
        },

        async getPermissionItems() {

            try {

                let response = await axios.get('api/permissions/getallpermission');
                this.permissions = response.data.paginate;


            } catch (error) {
                let message = error.response.data.message;
                let statusText = error.response.statusText;
                let status = error.response.status;

                if (status == 403) {
                    this.$router.push({ name: "unauthorize" });
                }
                if (status == 404) {
                    this.$router.push({ name: "pagenotfound" });
                }
                if (status == 500) {
                    this.$router.push({ name: "servererror" });
                }

                this.$swal({
                    position: "center",
                    icon: "error",
                    title: statusText != "" ? statusText : "Error",
                    title: message != "" ? message : "Invalid Request",
                    showConfirmButton: false,
                    timer: 2500,
                });
            } finally {
                this.isLoading = false;
            }
        },

        btnCancel() {
            this.dialog = false;
            this.clear();
        },

        clear() {
            this.$v.$reset();
            this.formData.permission = "";
            this.formData.role = "";
            this.formDataError = {
                role: [],
                permission: []
            };
        },

        showConfirmAlert(id) {
            this.$swal({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Delete record!",
            }).then(async (result) => {

                if (result.isConfirmed) { //confirm button


                    //Call delete Permission function
                    this.isLoading = true;
                    try {
                        const response = await axios.post("api/roles/deleteroles", { id: id });

                        if (response.data.success) {
                            // // send data to Sockot.IO Server
                            // this.$socket.emit("sendData", { action: "permission-delete" });
                            this.$swal({
                                position: "center",
                                icon: "success",
                                title: response.data.message,
                                showConfirmButton: false,
                                timer: 2500,
                            });
                            this.$refs.DataTableComponent.getItems();
                            this.$socket.emit("sendData", { action: "roles" });
                        }

                    } catch (error) {
                        let message = error.response.data.message;
                        let statusText = error.response.statusText;
                        let status = error.response.status;

                        if (status == 403) {
                            this.$router.push({ name: "unauthorize" });
                        }
                        if (status == 404) {
                            this.$router.push({ name: "pagenotfound" });
                        }
                        if (status == 500) {
                            this.$router.push({ name: "servererror" });
                        }

                        this.$swal({
                            position: "center",
                            icon: "error",
                            title: statusText != "" ? statusText : "Error",
                            title: message != "" ? message : "Invalid Request",
                            showConfirmButton: false,
                            timer: 2500,
                        });
                    } finally {
                        this.loading = false;
                    }

                }
            });
        },

    },
    computed: {
        formTitle() {
            return this.editMode ? "Edit Role" : "Add New Role";
        },
        roleErrors() {
            const errors = [];
            if (!this.$v.formData.role.$dirty) return errors;
            !this.$v.formData.role.required && errors.push("Role is required.");
            return errors;
        },
        permissionErrors() {
            const errors = [];
            if (!this.$v.formData.permission.$dirty) return errors;
            !this.$v.formData.permission.required && errors.push("Permission is required.");
            return errors;
        },
        ...mapGetters("rolespermissions", ["hasRole", "hasPermission"]),
    },
    mounted() {
        axios.defaults.headers.common["Authorization"] =
            "Bearer " + localStorage.getItem("access_token");

        this.getPermissionItems();
    },

};
</script>