<template>
    <div class="page">
        <div class="l-row l-max--960">
            <h1>Pages</h1>
            <router-link to="/admin/create/page" tag="button" class="btn btn-primary btn-small">Create New</router-link>
        </div>
        <div class="cards">
            <div class="card" v-for="page, k in pages" :key="k">
                <div class="card__details">
                    <h3>{{ page.title }}</h3> Last Updated: <span> {{ page.created_at | moment("MMMM Do YYYY") }} </span> by <span> {{ page.updated_by ? page.updated_by.name : 'User Not Found' }} </span>
                </div>
                <div class="card__utilities">
                    <router-link tag="span" :to="'/admin/edit/page/' + page.page_id"><a>Edit Page</a></router-link>
                    <router-link tag="span" :to="'/admin/create/page/' + page.page_id"><a>Create Child</a></router-link>
                    <span><a :href="page.url" target="_blank" rel="noopener noreferrer">View Page</a></span>
                    <button @click="alert(page.page_id)" class="delete">Delete Page <i class="la la-trash"></i></button>
                </div>
                <details v-if="page.children.length >= 1" class="card__children-wrap">
                    <summary>Show Children</summary>
                    <div class="card__child" v-for="child, l in page.children">
                        <div class="card__details">
                            <h4>{{ child.title }}</h4> Last Updated: <span> {{ child.updated_at | moment("MMMM Do YYYY") }} </span> by <span> {{ page.updated_by ? page.updated_by.name : 'User Not Found' }} </span>
                        </div>
                        <div class="card__utilities">
                            <router-link tag="span" :to="'/admin/edit/page/' + child.page_id"><a>Edit Page</a></router-link> <span><a :href="child.url" target="_blank" rel="noopener noreferrer">View Page</a></span> <button @click="alertDelete(page)" class="delete">Delete Page</button>
                        </div>
                    </div>
                </details>
            </div>
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
        alert(pageId) {

            let alertData = {
                intent: 'delete',
                type: 'page',
                id: pageId,
                method: 'deleteTarget',
                msg: 'WARNING: This will delete the page and any of its children.'
            }

            Bus.$emit('alert', alertData);
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
