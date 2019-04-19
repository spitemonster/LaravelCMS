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
                <router-link tag="li" to="/admin/create/page">
                    <a>Create New Page</a>
                </router-link>
                <router-link tag="li" to="/admin/view/templates">
                    <a>View Templates</a>
                </router-link>
                <router-link tag="li" to="/admin/view/pages">
                    <a>View Pages</a>
                </router-link>
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
                growlerMode: ''
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
            }
        },
        components: {
            fieldCard,
            createTemplate,
            createPage,
            router,
            loggedOut,
            growler
        },
        mounted () {
            Bus.$on('growl', (growler) => {
                this.growl(growler)
            })
        },
        beforeDestroy () {
        }
    }
</script>
<style lang="css">

</style>