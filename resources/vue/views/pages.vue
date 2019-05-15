<template>
    <section class="view__pages">
        <h1>View Pages</h1>
        <div class="page-card" v-for="page, k in pages" :key="k">
            <div class="page-card__details">
                <h3>{{ page.title }}</h3> Last Updated: <span> {{ page.created_at | moment("dddd, MMMM Do YYYY") }} </span> by <span> {{ page.updated_by.name }} </span>
            </div>
            <div class="page-card__utilities">
                <router-link tag="span" :to="'/admin/page/' + page.page_id + '/edit'"><a>Edit Page</a></router-link> <span><a :href="page.url" target="_blank" rel="noopener noreferrer">View Page</a></span> <button @click="deletePage(page.page_id)" class="delete">Delete Page <i class="la la-trash"></i></button>
            </div>
            <div class="child" v-for="child, l in page.children">
                <h4>{{ child.title }}</h4> Last Updated: <span> {{ child.updated_at | moment("dddd, MMMM Do YYYY")  }} </span> by <span> {{ child.updated_by.name }} </span>
                <div>
                    <router-link tag="span" :to="'/admin/page/' + child.page_id + '/edit'"><a>Edit Page</a></router-link> <span><a :href="child.url" target="_blank" rel="noopener noreferrer">View Page</a></span> <button @click="deletePage(child.page_id)" class="delete">Delete Page</button>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
    import axios from 'axios'
    import router from '../../js/admin.js'
    import Bus from '../../js/admin.js'

    export default {
        data () {
            return {
                pages: {}
            }
        },
        props: [],
        methods: {
            deletePage(pageId) {
                Bus.$emit('deletePage', pageId);
            }
        },
        beforeCreate () {
            axios.get('/page')
                .then((res) => {
                    this.pages = res.data
                })
        },
        mounted() {
            Bus.$on('pageDeleted', () => {
                axios.get('/page')
                    .then((res) => {
                        this.pages = res.data
                    })
            })
        }
    }
</script>
<style lang="css">
.child {
    margin-left: 2rem;
    position: relative;
}

.child::before {
    content: "˪";
    position: relative;
    left: 0;
    /*font-size: 32px;*/
}
</style>