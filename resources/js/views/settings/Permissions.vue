<template>
    <v-container>
        <v-row>
            <v-col>
                <BreadCrumbComponent :items="items"></BreadCrumbComponent>
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <DataTableComponent :parentName="'Permissions List'" :getPath="path" :headers="headers"
                    :toggleAdd="toggleDialog" ref="DataTableComponent" :addPermission="'permission-create'">

                    <template v-slot:item.actions="{ item }">
                        <v-icon small color="green" @click="toggleDialog(item.id)" v-if="hasPermission('permission-edit')">
                            mdi-pencil
                        </v-icon>
                        <v-icon small color="red" @click="showConfirmAlert(item.id)"
                            v-if="hasPermission('permission-delete')">
                            mdi-delete
                        </v-icon>
                    </template>

                </DataTableComponent>
            </v-col>
        </v-row>
        <v-dialog v-model="dialog" max-width="500px" persistent eager>
            <v-card>
                <v-card-title>
                    <span class="headline">{{ formTitle }}</span>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col class="mt-0 mb-0 pt-0 pb-0">
                                <v-text-field label="Permission" v-model="formData.permission"
                                    :error-messages="permissionErrors + formDataError.permission"
                                    @input="$v.formData.permission.$touch() + (formDataError.permission = [])"
                                    @blur="$v.formData.permission.$touch()"></v-text-field>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>

                <v-divider class=""></v-divider>

                <v-card-actions class="d-flex justify-end pb-5">
                    <v-btn color="#E0E0E0" @click="btnCancel" :disabled="buttonProps.disableCancel">
                        Cancel
                    </v-btn>
                    <v-btn color="primary" @click="btnSave" :disabled="buttonProps.disableSave"
                        :loading="buttonProps.loadingSave"
                        v-if="hasPermission('permission-edit') || hasPermission('permission-create')">
                        Save
                    </v-btn>
                </v-card-actions>

            </v-card>
        </v-dialog>
    </v-container>
</template>


<script>

import axios from "axios";
import { validationMixin } from "vuelidate";
import { required } from "vuelidate/lib/validators";
import { mapState, mapActions, mapGetters } from "vuex";


import DataTableComponent from "../components/DataTableComponent.vue";
import BreadCrumbComponent from "../components/BreadCrumbComponent.vue";


export default {
    mixins: [validationMixin],

    validations: {
        formData: {
            permission: { required },
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
                    text: "PERMISSIONS",
                    disabled: true,
                },
            ],
            path: "api/permissions/getpermission",
            headers: [
                {
                    text: 'Permission',
                    value: 'name',
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
                permission: "",
            },
            editMode: false,
            formDataError: {
                permission: [],
            },
            buttonProps: {
                disableSave: false,
                loadingSave: false,
                disableCancel: false,
            },
        };
    },

    watch: {

    },

    methods: {
        async toggleDialog(id) {

            this.clear();
            if (id != undefined) {
                try {
                    let response = await axios.get('api/permissions/selectpermission', { params: { id: id } });

                    this.formData.permission = response.data.permission.name;
                    this.formData.id = response.data.permission.id;

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

        async btnSave() {

            this.$v.$touch();
            this.formDataError = {
                permission: []
            };

            if (!this.$v.$error) {
                this.buttonProps.disableSave = true;
                this.buttonProps.disableCancel = true;
                this.buttonProps.loadingSave = true;

                const data = this.formData;

                try {
                    let request = 'api/permissions/createpermission';
                    if (this.editMode) { //Edit
                        request = 'api/permissions/updatepermission';
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
                        this.$socket.emit("sendData", { action: "permissions" });
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

        btnCancel() {
            this.dialog = false;
            this.clear();
        },

        clear() {
            this.$v.$reset();
            this.formData.permission = "";
            this.formDataError = {
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
                        const response = await axios.post("api/permissions/deletepermission", { id: id });

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
                            this.$socket.emit("sendData", { action: "permissions" });

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
            return this.editMode ? "Edit Permission" : "Add New Permission";
        },
        permissionErrors() {
            const errors = [];
            if (!this.$v.formData.permission.$dirty) return errors;
            !this.$v.formData.permission.required &&
                errors.push("Permission is required.");
            return errors;
        },
        ...mapGetters("rolespermissions", ["hasRole", "hasPermission"]),
    },
    mounted() {
        axios.defaults.headers.common["Authorization"] =
            "Bearer " + localStorage.getItem("access_token");
    },

};
</script>