<template>
    <div class="page">
        <h1>View Pages</h1>
        <div class="page-card" v-for="page, k in pages" :key="k">
            <div class="page-card__details">
                <h3>{{ page.title }}</h3> Last Updated: <span> {{ page.created_at | moment("dddd, MMMM Do YYYY") }} </span> by <span> {{ page.updated_by.name }} </span>
            </div>
            <div class="page-card__utilities">
                <router-link tag="span" :to="'/admin/page/' + page.page_id + '/edit'"><a>Edit Page</a></router-link> <span><a :href="page.url" target="_blank" rel="noopener noreferrer">View Page</a></span> <button @click="alertDelete(page)" class="delete">Delete Page <i class="la la-trash"></i></button>
            </div>
            <details v-if="page.children.length >= 1" class="page-card__children-wrap">
                <summary>Show Children</summary>
                <div class="page-card__child" v-for="child, l in page.children">
                    <div class="page-card__details">
                        <h4>{{ child.title }}</h4> Last Updated: <span> {{ child.updated_at | moment("dddd, MMMM Do YYYY") }} </span> by <span> {{ child.updated_by.name }} </span>
                    </div>
                    <div class="page-card__utilities">
                        <router-link tag="span" :to="'/admin/page/' + child.page_id + '/edit'"><a>Edit Page</a></router-link> <span><a :href="child.url" target="_blank" rel="noopener noreferrer">View Page</a></span> <button @click="alertDelete(page)" class="delete">Delete Page</button>
                    </div>
                </div>
            </details>
        </div>
    </div>
</template>
<script>
import axios from 'axios'
import router from '../../js/admin.js'
import Bus from '../../js/admin.js'

export default {
    data() {
        return {
            pages: {}
        }
    },
    props: [],
    methods: {
        deletePage(pageId) {
            Bus.$emit('deletePage', pageId);
        },
        alertDelete(page) {

            let alertData = {
                type: 'page',
                id: page.page_id,
                msg: 'WARNING: This will delete the page and any of its children.'
            }

            Bus.$emit('alertDelete', alertData);
        }
    },
    beforeCreate() {
        axios.get('/page')
            .then((res) => {
                this.pages = res.data
            })
    },
    mounted() {
        Bus.$on('deleted', (type) => {
            if (type === 'page') {
                axios.get('/page')
                    .then((res) => {
                        this.pages = res.data
                    })
            }
        })
    }
}

</script>
<style lang="css">
</style>
