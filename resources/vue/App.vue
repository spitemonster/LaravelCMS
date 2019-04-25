<template>
    <main class="dashboard">
        <nav class="dashboard__nav">
            <ul>
                <router-link tag="li" to="/admin">
                    <a>Dashboard</a>
                </router-link>
                <router-link tag="li" to="/admin/create/template">
                    <a>Create New Template</a>
                </router-link>
                <router-link tag="li" to="/admin/view/templates">
                    <a>View Templates</a>
                </router-link>
                <router-link tag="li" to="/admin/create/page">
                    <a>Create New Page</a>
                </router-link>
                <router-link tag="li" to="/admin/view/pages">
                    <a>View Pages</a>
                </router-link>
                <a href="/logout">Log Out</a>
            </ul>
        </nav>
        <div class="dashboard__body l-auto">
            <router-view></router-view>
        </div>
        <loggedOut v-if="logInError"></loggedOut>
        <growler :message="growlerMessage" :mode="growlerMode"></growler>
    </main>
</template>
<script>
    import fieldCard from './components/fieldCard.vue'
    import growler from './components/growler.vue'
    import createTemplate from './views/createTemplate.vue'
    import loggedOut from './components/loggedOut.vue'
    import createPage from './views/createPage.vue'
    import router from '../js/admin.js'
    import axios from 'axios'
    import Bus from '../js/admin.js'

    export default {
        name: 'App',
        data () {
            return {
                fields: [],
                logInError: false,
                growlerMessage: '',
                growlerMode: '',
                user: {}
            }
        },
        methods: {
            growl(growlerData) {
                let growler = document.querySelector('.growler')

                this.growlerMessage = growlerData.message
                this.growlerMode = growlerData.mode

                setTimeout(() => {
                    growler.classList.add('show')
                }, 100)

                setTimeout(() => {
                    growler.classList.remove('show')
                }, 5000)
            },
        },
        components: {
            fieldCard,
            createTemplate,
            createPage,
            router,
            loggedOut,
            growler
        },
        beforeMount() {
            axios.get('/user')
                .then((res) => {
                    this.api_token = res.data.api_token
                })
        },
        mounted () {
            Bus.$on('growl', (growler) => {
                this.growl(growler)
            })

            // Page Functionality
            Bus.$on('createPage', (pageData) => {
                // console.log(pageData)
                axios.post(`/page?api_token=${this.api_token}`, pageData)
                    .then((res) => {
                        let growlerData = {
                            mode: res.data.status,
                            message: res.data.message
                        }

                        this.$router.push({ name: 'viewPages' })
                        Bus.$emit('growl', growlerData);
                    })
            })

            Bus.$on('updatePage', (pageData) => {
                axios.patch(`/page?page_id=${pageData.page_id}&api_token=${this.api_token}`, pageData)
                    .then((res) => {
                        let growlerData = {
                            mode: res.data.status,
                            message: res.data.message
                        }

                        this.$router.push({ name: 'viewPages' })
                        Bus.$emit('growl', growlerData)
                    })
            })

            Bus.$on('deletePage', (pageId) => {
                axios.delete(`/page?page_id=${pageId}&api_token=${this.api_token}`)
                    .then((res) => {
                        let growlerData = {
                            mode: res.data.status,
                            message: res.data.message
                        }

                        Bus.$emit('pageDeleted')
                        Bus.$emit('growl', growlerData)
                    })
            })

            Bus.$on('createTemplate', (templateData) => {
                axios.post(`/template?api_token=${this.api_token}`, templateData)
                    .then((res) => {
                        let growlerData = {
                            mode: res.data.status,
                            message: res.data.message
                        }

                        this.$router.push({ name: 'viewTemplates' })
                        Bus.$emit('growl', growlerData)
                        Bus.$emit('templateCreated')
                    })
            })

            Bus.$on('updateTemplate', (templateData) => {
                axios.patch(`/template?template_id=${templateData.template_id}&api_token=${this.api_token}`, templateData)
                    .then((res) => {
                        let growlerData = {
                            mode: res.data.status,
                            message: res.data.message
                        }

                        this.$router.push({ name: 'viewTemplates' })
                        Bus.$emit('growl', growlerData)
                        Bus.$emit('templateCreated')
                    })
            })

            Bus.$on('deleteTemplate', (templateId) => {
                axios.delete(`/template?template_id=${templateId}&api_token=${this.api_token}`)
                    .then((res) => {
                        let growlerData = {
                            mode: res.data.status,
                            message: res.data.message
                        }

                        Bus.$emit('growl', growlerData)
                        Bus.$emit('templateDeleted')
                    })
            });
        },
        beforeDestroy () {
        }
    }
</script>
<style lang="css">

</style>