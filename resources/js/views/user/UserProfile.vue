<template>
    <v-container class="px-lg-10">
        <v-row>
            <v-col>
                <BreadCrumbComponent :items="items"></BreadCrumbComponent>
            </v-col>
        </v-row>
        <v-row>
            <v-col cols="12" lg="6" md="8">
                <v-card>
                    <v-card-title class="mb-0 pb-0">
                        <span class="headline">My Profile</span>
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-card-text class="px-5">
                        <v-row>
                            <v-col class="my-0 py-0">
                                <v-text-field name="name" v-model="formInput.name"
                                    :error-messages="nameErrors + formError.name" label="Full Name"
                                    @input="$v.formInput.name.$touch() + (formError.name = [])"
                                    @blur="$v.formInput.name.$touch()"></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col class="my-0 py-0">
                                <v-text-field name="email" v-model="formInput.email" label="Email" readonly></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col class="my-0 py-0">
                                <v-text-field name="password" v-model="formInput.password" label="Password"
                                    :error-messages="passwordErrors + formError.password" required
                                    @input="$v.formInput.password.$touch() + (formError.password = [])"
                                    @blur="$v.formInput.password.$touch() + dummyPassword" @keyup="passwordChange()"
                                    @focus="onFocus()" type="password"></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col class="my-0 py-0">
                                <v-text-field name="confirmPassword" v-model="formInput.confirmPassword"
                                    label="Confirm Password"
                                    :error-messages="confirmPasswordErrors + formError.confirmPassword" required
                                    @input="$v.formInput.confirmPassword.$touch() + (formError.confirmPassword = [])"
                                    @blur="$v.formInput.confirmPassword.$touch() + dummyPassword" @keyup="passwordChange()"
                                    @focus="onFocus()" type="password"></v-text-field>
                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions class="pa-0">
                        <v-btn color="primary" class="ml-6 mb-4 mr-1" @click="btnSave">Save</v-btn>
                        <v-btn to="/" class="mb-4">Cancel</v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>

import axios from "axios";

import { validationMixin } from "vuelidate";

import {
    required,
    maxLength,
    email,
    minLength,
    sameAs,
} from "vuelidate/lib/validators";

import BreadCrumbComponent from "../components/BreadCrumbComponent.vue";


import { mapState } from "vuex";

export default {

    mixins: [validationMixin],

    validations: {
        formInput: {
            name: {},
            password: {},
            confirmPassword: {},
            name: { required },
            password: { required, minLength: minLength(8) },
            confirmPassword: { required, sameAsPassword: sameAs("password") },
        },
    },

    components: {
        BreadCrumbComponent
    },

    data() {
        return {
            absolute: true,
            overlay: false,
            items: [
                {
                    text: "DASHBOARD",
                    disabled: false,
                    href: "#/",
                },
                {
                    text: "Users",
                    disabled: false,
                    href: "#/users",
                },
                {
                    text: "Profile",
                    disabled: true,
                },
            ],
            formInput: {
                name: "",
                email: "",
                password: "password",
                confirmPassword: "password",
            },
            formError: {
                name: [],
                email: [],
                password: [],
                confirmPassword: [],
            },
            passwordHasChanged: false,
        };
    },

    watch: {
        currentUser: {
            handler() {
                if (this.currentUser != null) {
                    this.formInput.name = this.currentUser.name;
                    this.formInput.email = this.currentUser.email;
                }
            },
        },
    },

    methods: {
        onFocus() {
            if (!this.passwordHasChanged) {
                this.formInput.password = "";
                this.formInput.confirmPassword = "";
            }
        },
        passwordChange() {
            if (this.formInput.password || this.formInput.confirmPassword) {
                this.passwordHasChanged = true;
            } else {
                this.passwordHasChanged = false;
            }
        },
        showError(error) {
            // if unauthenticated (401)
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
        },

        async btnSave() {

            this.$v.$touch();
            this.formError = {
                name: [],
                email: [],
                password: [],
                confirmPassword: [],
            };


            if (!this.$v.$error) {
                this.disabled = true;
                this.overlay = true;

                const formData = {
                    name: this.formInput.name,
                    password: this.formInput.password,
                    confirmPassword: this.formInput.confirmPassword,
                };

                if (this.formInput.password == "password" && this.formInput.confirmPassword == "password" && !this.passwordHasChanged) {
                    formData.password = "";
                    formData.confirmPassword = "";
                }


                try {
                    const response = await axios.post("/api/user/updateprofile/" + this.currentUser.id, formData);

                    if (!response.data.success) {
                        let errors = response.data.error;
                        let errorNames = Object.keys(response.data.error);
                        errorNames.forEach((value) => {
                            this.formError[value].push(errors[value]);
                        });
                    } else {
                        // send data to Sockot.IO Server
                        this.$socket.emit("sendData", { action: "users" });
                        
                    }

                    this.$swal({
                        position: "center",
                        icon: response.data.success ? "success" : "error",
                        title: response.data.message,
                        showConfirmButton: false,
                        timer: 2500,
                    });


                    this.passwordHasChanged = false;
                } catch (error) {
                    this.showError(error);
                } finally {
                    this.overlay = false;
                    this.disabled = false;
                }

            }


        }
    },
    computed: {
        nameErrors() {
            const errors = [];
            if (!this.$v.formInput.name.$dirty) return errors;
            !this.$v.formInput.name.required && errors.push("Name is required.");
            return errors;
        },
        passwordErrors() {
            const errors = [];
            if (!this.$v.formInput.password.$dirty) return errors;
            !this.$v.formInput.password.required && errors.push("Password is required.");
            !this.$v.formInput.password.minLength &&
                errors.push("Password must be atleast 8 characters.");
            return errors;
        },
        confirmPasswordErrors() {
            const errors = [];
            if (!this.$v.formInput.confirmPassword.$dirty) return errors;
            !this.$v.formInput.confirmPassword.required &&
                errors.push("Confirmation Password is required.");
            !this.$v.formInput.confirmPassword.sameAsPassword &&
                errors.push("Two passwords did not match.");
            return errors;
        },
        dummyPassword() {
            if (!this.formInput.password && !this.formInput.confirmPassword) {
                this.formInput.password = "password";
                this.formInput.confirmPassword = "password";
            }
        },
        ...mapState("currentUser", ["currentUser"]),
    },
    mounted() {
        axios.defaults.headers.common["Authorization"] =
            "Bearer " + localStorage.getItem("access_token");

        this.formInput.name = this.currentUser.name;
        this.formInput.email = this.currentUser.email;
    },
};
</script>