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
                type: "template",
                id: template.template_id,
                msg: `WARNING: ${ template.pages.length } page(s) are currently using this template.This will delete the template and its history.Any pages using this template will also be deleted.`
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
        Bus.$on('deleted', (type) => {
            if (type === 'template') {
                axios.get('/template')
                    .then((res) => {
                        this.templates = res.data
                    })
            }
        });
    }
}

</script>
<style lang="css">
</style>
