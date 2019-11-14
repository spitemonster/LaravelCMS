<template>
    <div class="page">
        <h1>View Templates</h1>
        <div class="card" v-for="template, k in templates" :key="k">
            <div class="card__details">
                <h3>{{ template.name }}</h3> Last Updated: <span> {{ template.created_at | moment("dddd, MMMM Do YYYY") }} </span> by <span> </span>
            </div>
            <div class="card__utilities">
                <router-link tag="span" :to="'/admin/edit/template/' + template.template_id"><a>Edit Template</a></router-link><button @click="alert(template)" class="delete">Delete Template<i class="la la-trash"></i></button>
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
        alert(template) {
            let alertData = {
                intent: 'delete',
                type: 'template',
                id: template.template_id,
                method: 'deleteTarget',
                msg: `WARNING: ${ template.pages.length } page(s) are currently using this template.This will delete the template and its history.Any pages using this template will also be deleted.`
            }

            Bus.$emit('alert', alertData)
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
