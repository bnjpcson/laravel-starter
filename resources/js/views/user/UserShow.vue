<template>
    <v-container class="px-lg-10">
        <v-row>
            <v-col>
                <BreadCrumbComponent :items="items"></BreadCrumbComponent>
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <DataTableComponent :parentName="'Users List'" :getPath="path" :headers="headers" :toggleAdd="toggleDialog"
                    ref="DataTableComponent" :addPermission="'user-create'">

                    <template v-slot:item.active="{ item }">
                        <v-chip small color="success" v-if="item.active == 1">
                            Active
                        </v-chip>
                        <v-chip small color="warning" v-if="item.active == 0">
                            Inactive
                        </v-chip>
                    </template>

                    <template v-slot:item.roles="{ item }">
                        <v-chip small color="secondary" v-if="item.roles[0] != null" @click="toggleRoleDialog(item.id)">
                            {{ item.roles[0].name }}
                        </v-chip>
                        <v-chip small v-if="item.roles.length > 1">
                            {{ "+" }}
                            {{
                                item.roles.length > 1
                                ? item.roles.length - 1
                                : item.roles[0].name
                            }}
                            {{ "others" }}
                        </v-chip>
                    </template>

                    <template v-slot:item.actions="{ item }" v-if="hasPermission('user-edit') ||
                        hasPermission('user-delete')">
                        <v-icon small color="blue" v-if="(item.id == 1) && hasPermission('user-edit')"
                            @click="toggleDialog(item.id)">
                            mdi-eye
                        </v-icon>
                        <v-icon small color="green" v-if="item.id != 1 && hasPermission('user-edit')"
                            @click="toggleDialog(item.id)">
                            mdi-pencil
                        </v-icon>
                        <v-icon small color="red" v-if="item.id != 1 && hasPermission('user-delete')"
                            @click="showConfirmAlert(item.id)">
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
                                <v-text-field label="Full Name" v-model="formData.name"
                                    :error-messages="nameErrors + formDataError.name"
                                    @input="$v.formData.name.$touch() + (formDataError.name = [])"
                                    @blur="$v.formData.name.$touch()" :disabled="buttonProps.disableSave"></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col class="mt-0 mb-0 pt-0 pb-0">
                                <v-text-field label="Email" v-model="formData.email"
                                    :error-messages="emailErrors + formDataError.email"
                                    @input="$v.formData.email.$touch() + (formDataError.email = [])"
                                    @blur="$v.formData.email.$touch()" :disabled="isEmailDisabled"></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col class="mt-0 mb-0 pt-0 pb-0">
                                <v-text-field label="Password" v-model="formData.password"
                                    :error-messages="passwordErrors + formDataError.password"
                                    @input="$v.formData.password.$touch() + (formDataError.password = [])"
                                    @blur="$v.formData.password.$touch() + getDummyPassword" @click="togglePassword"
                                    type="password" :disabled="buttonProps.disableSave"></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col class="mt-0 mb-0 pt-0 pb-0">
                                <v-text-field label="Confirm Password" v-model="formData.confirmpassword"
                                    :error-messages="confirmpasswordErrors + formDataError.confirmpassword"
                                    @input="$v.formData.confirmpassword.$touch() + (formDataError.confirmpassword = [])"
                                    @blur="$v.formData.confirmpassword.$touch() + getDummyPassword" @click="togglePassword"
                                    type="password" :disabled="buttonProps.disableSave"></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col class="mt-0 mb-0 pt-0 pb-0">
                                <v-autocomplete v-model="formData.roles" :items="roles" item-text="name" item-value="name"
                                    label="Roles" multiple chips :disabled="buttonProps.disableSave"
                                    :error-messages="rolesErrors + formDataError.roles"
                                    @input="$v.formData.roles.$touch() + (formDataError.roles = [])">
                                    <template v-slot:selection="data">
                                        <v-chip color="secondary" v-bind="data.attrs" :input-value="data.selected"
                                            @click="data.select">
                                            {{ data.item }}
                                        </v-chip>
                                    </template>
                                </v-autocomplete>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col class="mt-0 mb-0 pt-0 pb-0">
                                <v-switch v-model="formData.active" :label="getActive" color="success"></v-switch>
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
                        :loading="buttonProps.loadingSave">
                        Save
                    </v-btn>
                </v-card-actions>

            </v-card>
        </v-dialog>

        <v-dialog v-model="rolesData.dialog" max-width="600" eager>
            <v-card>
                <v-card-title>
                    <span>Roles</span>
                    <v-spacer></v-spacer>
                    <v-text-field v-model="rolesData.search" label="Search"></v-text-field>
                    <v-spacer></v-spacer>
                    <v-btn class="ma-2" text icon @click="rolesData.dialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text>
                    <v-card>
                        <v-data-table :headers="rolesData.headers" :items="rolesData.items"
                            :expanded.sync="rolesData.expanded" item-key="name" show-expand class="elevation-1"
                            :search="rolesData.search" :options.sync="rolesData.options">

                            <template v-slot:expanded-item="{ headers, item }">
                                <td :colspan="headers.length">
                                    <v-chip-group column>
                                        <v-chip color="secondary" v-for="item in item.permissions" :key="item.id">
                                            {{ item.name }}
                                        </v-chip>
                                    </v-chip-group>
                                </td>
                            </template>

                        </v-data-table>
                    </v-card>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>

import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, email, minLength, sameAs } from "vuelidate/lib/validators";
import { mapState, mapActions, mapGetters } from "vuex";


import DataTableComponent from "../components/DataTableComponent.vue";
import BreadCrumbComponent from "../components/BreadCrumbComponent.vue";



export default {
    mixins: [validationMixin],

    validations: {
        formData: {
            name: { required },
            email: { required, email, minLength },
            password: { required, minLength: minLength(6) },
            confirmpassword: { required, sameAsPassword: sameAs('password') },
            roles: { required },
        },
    },

    components: {
        DataTableComponent,
        BreadCrumbComponent
    },

    data() {
        return {
            isLoading: true,
            items: [
                {
                    text: "Dashboard",
                    disabled: false,
                    href: "#/",
                },
                {
                    text: "USERS",
                    disabled: true,
                },
            ],
            path: "api/user/getusers",
            headers: [
                {
                    text: 'Fullname',
                    value: 'name',
                },
                {
                    text: 'Email',
                    value: 'email',
                },
                {
                    text: 'Status',
                    value: 'active',
                },
                {
                    text: 'Last Login',
                    value: 'last_login',
                },
                {
                    text: 'Roles',
                    value: 'roles',
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
                name: "",
                email: "",
                password: "",
                confirmpassword: "",
                roles: [],
                active: true

            },
            defaultData: {
                id: 0,
                name: "",
                email: "",
                password: "",
                confirmpassword: "",
                roles: [],
                active: true
            },
            formDataError: {
                name: [],
                email: [],
                password: [],
                confirmpassword: [],
                roles: [],

            },
            defaultError: {
                name: [],
                email: [],
                password: [],
                confirmpassword: [],
                roles: [],

            },
            editMode: false,
            buttonProps: {
                disableSave: false,
                loadingSave: false,
                disableCancel: false,
            },
            roles: [],
            dummyPassword: "password",
            isPasswordChange: false,
            isEmailDisabled: false,
            rolesData: {
                dialog: false,
                search: "",
                options: {},
                pageOptions: {
                    path: "",
                    per_page: -1,
                },
                expanded: [],
                headers: [
                    {
                        text: 'Role',
                        align: 'start',
                        value: 'name',
                    },
                    { text: '', value: 'data-table-expand' },
                ],
                items: [],
            }
        };
    },

    watch: {

    },

    methods: {
        async getRolesItems() {
            try {

                let response = await axios.get('api/roles/getallroles');
                this.roles = response.data.paginate;

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

        async toggleDialog(id) {
            if (id == 1) {
                this.buttonProps.disableSave = true;
            } else {
                this.buttonProps.disableSave = false;
            }

            this.clear();


            if (id != undefined) {

                this.isEmailDisabled = true;

                try {
                    let response = await axios.get('api/user/selectuser', { params: { id: id } });

                    let roles = [];
                    response.data.user.roles.forEach(element => {
                        roles.push(element.name);
                    });

                    this.formData.roles = roles;
                    this.formData.id = response.data.user.id;
                    this.formData.name = response.data.user.name;
                    this.formData.email = response.data.user.email;
                    this.formData.active = response.data.user.active == 1 ? true : false;
                    this.formData.password = this.dummyPassword;
                    this.formData.confirmpassword = this.dummyPassword;

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

        async toggleRoleDialog(id) {
            try {
                let response = await axios.get('/api/user/selectuserrolespermission', { params: { id: id } });

                let item = [];

                response.data.data.roles.forEach(element => {
                    let name = element[0].name;

                    let permissions = element[0].permissions;
                    let data = {
                        name: name,
                        permissions: permissions
                    };
                    item.push(data);
                });

                this.rolesData.items = item;
                this.rolesData.dialog = true;
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

            if (!this.$v.$error) {
                this.buttonProps.disableSave = true;
                this.buttonProps.disableCancel = true;
                this.buttonProps.loadingSave = true;

                this.formDataError.name = this.defaultError.name;
                this.formDataError.email = this.defaultError.email;
                this.formDataError.password = this.defaultError.password;
                this.formDataError.confirmpassword = this.defaultError.confirmpassword;
                this.formDataError.roles = this.defaultError.roles;

                if (this.formData.active) {
                    this.formData.active = 1;
                } else {
                    this.formData.active = 0;
                }

                let data = {
                    id: this.formData.id,
                    name: this.formData.name,
                    email: this.formData.email,
                    password: this.formData.password,
                    confirmpassword: this.formData.confirmpassword,
                    roles: this.formData.roles,
                    active: this.formData.active
                };

                try {
                    let request = 'api/user/createuser';
                    if (this.editMode) { //Edit
                        request = 'api/user/updateuser';
                        if (!this.isPasswordChange) {
                            data.password = "";
                            data.confirmpassword = "";
                        }
                    }

                    let response = await axios.post(request, data);

                    if (response.data.success) {
                        this.$swal({
                            position: "center",
                            icon: "success",
                            title: "Record has been saved",
                            showConfirmButton: false,
                            timer: 2500,
                        });
                        this.dialog = false;
                        this.$refs.DataTableComponent.getItems();
                        this.$socket.emit("sendData", { action: "users" });
                    }

                } catch (error) {
                    if (error.response.data.errors) {
                        let errors = error.response.data.errors;
                        let errorNames = Object.keys(error.response.data.errors);

                        errorNames.forEach(value => {
                            this.formDataError[value].push(errors[value]);
                        });

                    } else {
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
                } finally {
                    this.buttonProps.disableSave = false;
                    this.buttonProps.disableCancel = false;
                    this.buttonProps.loadingSave = false;
                }

            }

        },

        togglePassword() {
            if (this.formData.password || this.formData.confirmpassword) {
                this.isPasswordChange = true;
            } else {
                this.isPasswordChange = false;
            }

            if (this.formData.password != "" && this.formData.confirmpassword != "" && this.editMode) {
                this.formData.password = "";
                this.formData.confirmpassword = "";
            }
        },

        btnCancel() {
            this.dialog = false;
            this.clear();
        },

        clear() {
            this.$v.$reset();

            this.isEmailDisabled = false;

            this.formData.id = this.defaultData.id;
            this.formData.name = this.defaultData.name;
            this.formData.email = this.defaultData.email;
            this.formData.password = this.defaultData.password;
            this.formData.confirmpassword = this.defaultData.confirmpassword;
            this.formData.roles = this.defaultData.roles;

            this.formDataError.name = this.defaultError.name;
            this.formDataError.email = this.defaultError.email;
            this.formDataError.password = this.defaultError.password;
            this.formDataError.confirmpassword = this.defaultError.confirmpassword;
            this.formDataError.roles = this.defaultError.roles;

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
                        const response = await axios.post("api/user/deleteuser", { id: id });

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
                            this.$socket.emit("sendData", { action: "users" });
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
        getActive() {
            return this.formData.active ? "Active" : "Inactive";
        },
        formTitle() {
            return this.editMode ? "Edit User" : "Add New User";
        },
        nameErrors() {
            const errors = [];
            if (!this.$v.formData.name.$dirty) return errors;
            !this.$v.formData.name.required &&
                errors.push("Name is required.");
            return errors;
        },
        emailErrors() {
            const errors = [];
            if (!this.$v.formData.email.$dirty) return errors;
            !this.$v.formData.email.required &&
                errors.push("Email is required.");
            !this.$v.formData.email.email &&
                errors.push("Please provide a valid email address.");
            return errors;
        },
        passwordErrors() {
            const errors = [];
            if (!this.$v.formData.password.$dirty) return errors;
            !this.$v.formData.password.required &&
                errors.push("Password is required.");
            !this.$v.formData.password.minLength &&
                errors.push("Password must be 6 characters and above.");
            return errors;
        },
        confirmpasswordErrors() {
            const errors = [];
            if (!this.$v.formData.confirmpassword.$dirty) return errors;
            !this.$v.formData.confirmpassword.required &&
                errors.push("Confirm Password is required.");
            !this.$v.formData.confirmpassword.sameAsPassword &&
                errors.push("Two passwords do not match.");
            return errors;
        },
        rolesErrors() {
            const errors = [];
            if (!this.$v.formData.roles.$dirty) return errors;
            !this.$v.formData.roles.required &&
                errors.push("Roles are required.");
            return errors;
        },
        getDummyPassword() {
            if (!this.formData.password && !this.formData.confirmpassword && this.editMode) {
                this.isPasswordChange = false;
                this.formData.password = this.dummyPassword;
                this.formData.confirmpassword = this.dummyPassword;
            }
        },
        ...mapGetters("rolespermissions", ["hasRole", "hasPermission"]),

    },
    mounted() {
        axios.defaults.headers.common["Authorization"] =
            "Bearer " + localStorage.getItem("access_token");
        this.getRolesItems();
    },
};
</script>