<template>
    <v-container fluid fill-height style="background-image: url('https://i2.wp.com/files.123freevectors.com/wp-content/original/156195-abstract-light-grey-background-design.jpg?w=500&q=95'); background-size: cover;background-position: center;
  background-repeat: no-repeat;">
        <v-layout align-center justify-center>
            <v-flex xs12 sm8 md6 lg4>
                <v-overlay :absolute="absolute" :value="overlay">
                    <v-progress-circular :size="70" :width="7" color="primary" indeterminate></v-progress-circular>
                </v-overlay>

                <v-card class="rounded-xxl" elevation="10">
                    <v-card-text>
                        <v-img width="150" height="150" class="text-center mx-auto"
                            src="https://www.freepnglogos.com/uploads/lion-logo-png/lion-download-college-logos-pierpont-3.png">
                        </v-img>

                        <div class="mr-5 ml-5">
                            <v-alert v-model="alert" border="left" dismissible type="error" v-for="(error, index) in errors"
                                :key="index">
                                <span>{{ error }}</span>
                            </v-alert>
                            <v-form>
                                <v-text-field v-model="formData.email" class="rounded-xl" outlined
                                    prepend-inner-icon="mdi-account" label="Email"
                                    :error-messages="emailErrors + formDataError.email"
                                    @input="$v.formData.email.$touch() + (formDataError.email = [])"
                                    @blur="$v.formData.email.$touch()" @keyup="alert = false">
                                </v-text-field>

                                <v-text-field v-model="formData.password" class="rounded-xl" outlined
                                    prepend-inner-icon="mdi-key" :append-icon="isVisible ? 'mdi-eye-off' : 'mdi-eye'"
                                    @click:append="isVisible = !isVisible" label="Password"
                                    :type="isVisible ? 'text' : 'password'"
                                    :error-messages="passwordErrors + formDataError.password"
                                    @input="$v.formData.password.$touch() + (formDataError.password = [])"
                                    @blur="$v.formData.password.$touch()" @keyup="alert = false">>
                                </v-text-field>
                                <v-btn large class="w-100 mb-3 rounded-xl" color="primary"
                                    @click="toggleLogin()">Login</v-btn>
                            </v-form>
                        </div>
                    </v-card-text>
                </v-card>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<style></style>

<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";


export default {
    mixins: [validationMixin],

    validations: {
        formData: {
            email: { required, email },
            password: { required },
        },
    },

    data() {
        return {
            formData: {
                email: "",
                password: "",
            },
            formDataError: {
                email: [],
                password: []
            },
            absolute: true,
            overlay: false,
            isVisible: false,
            errors: [],
            alert: false
        };
    },
    computed: {
        emailErrors() {
            const errors = [];
            if (!this.$v.formData.email.$dirty) return errors;
            !this.$v.formData.email.required && errors.push("Email is required.");
            !this.$v.formData.email.email && errors.push("Please provide a valid email address.");
            return errors;
        },

        passwordErrors() {
            const errors = [];
            if (!this.$v.formData.password.$dirty) return errors;
            !this.$v.formData.password.required && errors.push("Password is required.");
            return errors;
        },
    },

    methods: {
        async toggleLogin() {

            const data = this.formData;


            this.$v.$touch();

            if (!this.$v.$error) {
                this.overlay = true;
                const data = this.formData;


                try {
                    const response = await axios.post("/api/auth/login", data);

                    if (!response.data.success) {
                        this.errors = response.data.error;
                        this.alert = true;
                    } else {
                        localStorage.setItem("access_token", response.data.access_token);
                        this.$router.push("/").catch((e) => { });
                    }
                } catch (error) {
                    if (error.response.status === 422) {
                        if (error.response.data.message != undefined) {
                            this.errors = [error.response.data.message];
                            this.alert = true;
                        }
                        if (error.response.data.errors.email != undefined) {
                            this.formDataError.email = error.response.data.errors.email;
                        }
                        if (error.response.data.errors.password != undefined) {
                            this.formDataError.password = error.response.data.errors.password;
                        }
                    } else {
                        this.$swal({
                            position: "center",
                            icon: 'error',
                            title: error.response.statusText != undefined ? error.response.statusText : "Invalid Request",
                            text: error.response.data.message != undefined ? error.response.data.message : "Please try again later.",
                            showConfirmButton: false,
                            timer: 2500,
                        });
                    }
                } finally {
                    this.overlay = false;
                }
            }
        }
    },
    mounted() {
        let self = this;
        window.addEventListener("keyup", function (event) {
            if (event.keyCode === 13) {
                self.toggleLogin();
            }
        });
    }
}

</script>