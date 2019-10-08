<template>
    <div class="page">
        <h1>View Templates</h1>
        <div class="page-card" v-for="template, k in templates" :key="k">
            <div class="page-card__details">
                <h3>{{ template.name }}</h3> Last Updated: <span> {{ template.created_at | moment("dddd, MMMM Do YYYY") }} </span> by <span> </span>
            </div>
            <div class="page-card__utilities">
                <router-link tag="span" :to="'/admin/template/' + template.template_id + '/edit'"><a>Edit Template</a></router-link><button @click="alertDelete(template)" class="delete">Delete Template<i class="la la-trash"></i></button>
            </div>
        </div>
    </div>
</template>
<script>
import fieldCard from '../components/fieldCard.vue'
import draggable from 'vuedraggable'
import Bus from '../../js/admin.js'
import uuidv4 from 'uuid/v4'
import axios from 'axios'

export default {
    data() {
        return {
            templates: {}
        }
    },
    props: [],
    methods: {
        alertDelete(template) {
            let alertData = {
                type: "deleteTemplate",
                template: template
            }

            Bus.$emit('alertDelete', alertData)
        },
        deleteTemplate(templateId) {
            Bus.$emit('deleteTemplate', templateId);
        }
    },
    beforeCreate() {
        axios.get('/template')
            .then((res) => {
                this.templates = res.data
            })
    },
    mounted() {
        Bus.$on('templateDeleted', () => {
            axios.get('/template')
                .then((res) => {
                    this.templates = res.data
                })
        });
    }
}

</script>
<style lang="css">
</style>
