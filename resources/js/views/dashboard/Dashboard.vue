<template>
    <v-container>
        <v-dialog v-model="dialog" width="60%">
            <v-img @click="dialog = false" lazy-src="https://picsum.photos/id/11/10/6" width="100%" :src="selectedPic">
                <template v-slot:placeholder>
                    <div class="d-flex align-center justify-center fill-height">
                        <v-progress-circular color="grey-lighten-4" indeterminate></v-progress-circular>
                    </div>
                </template>
            </v-img>
        </v-dialog>
        <v-row>
            <v-col cols="12" sm="6" md="4" lg="3" v-for="(item, index) in  500" :key="index">
                <v-img @click="setSelectedPic(index)" lazy-src=" https://picsum.photos/id/11/10/6" width="100%"
                    :src="`https://picsum.photos/id/${index}/536/354`">
                    <template v-slot:placeholder>
                        <div class="d-flex align-center justify-center fill-height">
                            <v-progress-circular color="grey-lighten-4" indeterminate></v-progress-circular>
                        </div>
                    </template></v-img>
            </v-col>
        </v-row>
    </v-container>
</template>



<script>
import axios from "axios";

export default {
    data() {
        return {
            dialog: false,
            selectedPic: ""
        };
    },
    methods: {
        setSelectedPic(index) {
            this.dialog = true
            this.selectedPic = 'https://picsum.photos/id/' + index + '/536/354';;
        }
    },
    computed: {

    },
    watch: {

    },
    mounted() {
        axios.defaults.headers.common["Authorization"] = "Bearer " + localStorage.getItem("access_token");
    },
};
</script>