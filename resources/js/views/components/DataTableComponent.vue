<template>
    <v-card>
        <v-card-title>
            <v-container class="d-flex d-xs-flex d-sm-flex d-md-none d-lg-none d-xl-none">
                <v-row class="d-flex justify-space-between align-center">
                    <v-col cols="8" class="d-flex justify-start">
                        {{ parentName }}
                    </v-col>
                    <v-col cols="4" class="d-flex justify-end">
                        <v-btn color="primary" fab dark class="mb-2" @click="toggleAdd()"
                            v-if="hasPermission(addPermission)">
                            <v-icon>mdi-plus</v-icon>
                        </v-btn>
                    </v-col>
                    <v-col cols="12" class="d-flex justify-center">
                        <v-text-field class="w-100" v-model="search" append-icon="mdi-magnify" label="Search" single-line
                            @click:append="toggleSearch">
                        </v-text-field>
                    </v-col>
                </v-row>
            </v-container>

            <v-container class="d-none d-md-flex d-lg-flex">
                <v-row class="d-flex justify-space-between  align-center d-none d-lg-flex d-xl-none">
                    <v-col cols="4" class="d-flex justify-start">
                        {{ parentName }}
                    </v-col>
                    <v-col cols="6" class="d-flex justify-center">
                        <v-text-field class="w-100" v-model="search" append-icon="mdi-magnify" label="Search" single-line
                            @click:append="toggleSearch">
                        </v-text-field>
                    </v-col>
                    <v-col cols="2" class="d-flex justify-end">
                        <v-btn color="primary" fab dark class="mb-2" @click="toggleAdd()"
                            v-if="hasPermission(addPermission)">
                            <v-icon>mdi-plus</v-icon>
                        </v-btn>
                    </v-col>
                </v-row>
            </v-container>
        </v-card-title>

        <v-data-table :headers="headers" :items="items" :loading="isLoading" class="elevation-1" :search="search"
            :footerProps="footerProps" :options.sync="options">

            <template v-for="slot in Object.keys($scopedSlots)" :slot="slot" slot-scope="scope">
                <slot :name="slot" v-bind="scope"></slot>
            </template>

            <template v-slot:footer.page-text>
                <div class="d-flex flex-row justify-center align-middle">
                    <span class="d-none d-sm-flex  d-flex align-center">{{ getPageText }}</span>
                    <v-btn class="d-flex" icon @click="getItems('first')" :disabled="isDisabled.btnFirstPage">
                        <v-icon>
                            mdi-chevron-double-left
                        </v-icon>
                    </v-btn>
                    <v-btn class="d-flex" icon @click="getItems('prev')" :disabled="isDisabled.btnPrevPage">
                        <v-icon>
                            mdi-chevron-left
                        </v-icon>
                    </v-btn>

                    <v-btn class="d-flex" icon @click="getItems('next')" :disabled="isDisabled.btnNextPage">
                        <v-icon>
                            mdi-chevron-right
                        </v-icon>
                    </v-btn>

                    <v-btn class="d-flex" icon @click="getItems('last')" :disabled="isDisabled.btnLastPage">
                        <v-icon>
                            mdi-chevron-double-right
                        </v-icon>
                    </v-btn>
                </div>
            </template>

        </v-data-table>

    </v-card>
</template>

<script>
import axios from 'axios';
import { mapState, mapActions, mapGetters } from "vuex";


export default {
    props: ['parentName', 'getPath', 'headers', 'toggleAdd', 'addPermission'],
    data() {
        return {
            search: "",
            options: {},
            pageOptions: {
                path: "",
                per_page: -1,
            },
            items: [],
            isLoading: true,
            footerProps: {
                pageText: "",
                prevIcon: null,
                nextIcon: null,
                disablePagination: true,
                'items-per-page-options': [10, 25, 50, 100, 500]
            },
            isDisabled: {
                btnFirstPage: true,
                btnNextPage: true,
                btnPrevPage: true,
                btnLastPage: true,
            },
        };
    },
    watch: {
        options: {
            handler() {
                if (this.options.itemsPerPage != this.pageOptions.per_page)
                    this.getItems();
            },
            deep: true,
        },
        search: {
            handler() {
                if (this.search == "") {
                    this.getItems();
                } else {
                    this.isDisabled.btnFirstPage = true;
                    this.isDisabled.btnNextPage = true;
                    this.isDisabled.btnPrevPage = true;
                    this.isDisabled.btnLastPage = true;
                }
            },
            deep: true,
        },
    },
    methods: {
        toggleSearch() {
            this.getItems();
        },
        async getItems(toggle) {

            let { itemsPerPage } = this.options;
            this.pageOptions.per_page = itemsPerPage;


            try {

                let url = this.pageOptions.path;

                if (toggle == "first") {
                    url = this.pageOptions.first_page_url;
                } else if (toggle == "prev") {
                    url = this.pageOptions.prev_page_url;
                } else if (toggle == "next") {
                    url = this.pageOptions.next_page_url;
                } else if (toggle == "last") {
                    url = this.pageOptions.last_page_url;
                }

                let response = await axios.get(url, {
                    params: { per_page: this.pageOptions.per_page, search: this.search },
                });

                this.pageOptions = response.data.paginate;
                this.items = this.pageOptions.data;

                this.isDisabled.btnFirstPage = false;
                this.isDisabled.btnPrevPage = false;
                this.isDisabled.btnNextPage = false;
                this.isDisabled.btnLastPage = false;

                if (this.pageOptions.current_page == 1) {
                    this.isDisabled.btnFirstPage = true;
                    this.isDisabled.btnPrevPage = true;
                }
                if (this.pageOptions.current_page == this.pageOptions.last_page) {
                    this.isDisabled.btnNextPage = true;
                    this.isDisabled.btnLastPage = true;
                }

            } catch (error) {
                let message = error.response.data.message;
                let statusText = error.response.statusText;
                let status = error.response.status;
                
                if(status == 403){
                    this.$router.push({ name: "unauthorize" });
                }

                this.$swal({
                    position: "center",
                    icon: 'error',
                    title: statusText,
                    text: message,
                    showConfirmButton: false,
                    timer: 2500,
                });
            } finally {
                this.isLoading = false;
            }
        },
        websocket() {
            // Socket.IO fetch data
            this.$options.sockets.sendData = (data) => {
                let action = data.action;
                if (action == "users" || action == "roles" || action == "permissions") {
                    this.getItems();
                }
            };
        },
    },

    computed: {
        getPageText() {
            let from = (this.pageOptions.from != null) ? this.pageOptions.from : 0;
            let to = (this.pageOptions.to != null) ? this.pageOptions.to : 0;
            if (this.pageOptions.per_page == -1) {
                from = 1;
                to = this.pageOptions.total;
            }
            if (this.pageOptions.from !== undefined)
                return from + "-" + to + " of " + this.pageOptions.total;
            else
                return "";
        },
        formTitle() {
            return this.editMode ? "Edit " + this.parentName : "Add " + this.parentName;
        },

        ...mapGetters("rolespermissions", ["hasRole", "hasPermission"]),
    },

    mounted() {
        this.pageOptions.path = this.getPath;
        this.websocket();
    }

}
</script>