<template>
    <main class="dashboard">
        <nav class="dashboard__nav">
            <ul>
                <router-link tag="li" to="/admin">
                    <a>Dashboard</a>
                </router-link>
                <router-link tag="li" to="/admin/view/templates">
                    <a>Templates</a>
                </router-link>
                <router-link tag="li" to="/admin/view/pages">
                    <a>Pages</a>
                </router-link>
                <router-link tag="li" to="/admin/view/tags">
                    <a>Tags</a>
                </router-link>
                <router-link tag="li" to="/admin/view/users">
                    <a>Users</a>
                </router-link>
                <router-link tag="li" to="/admin/view/media">
                    <a>Media</a>
                </router-link>
                <li>
                    <a href="/logout">Log Out</a>
                </li>
            </ul>
        </nav>
        <div class="dashboard__body l-auto animate">
            <router-view></router-view>
        </div>
        <div class="dashboard__nav-toggle" @click="toggleNav">
        </div>
        <loggedOut v-if="logInError"></loggedOut>
        <growler :message="growlerMessage" :mode="growlerMode"></growler>
        <alert :alertData="alertData"></alert>
        <mediaWindow></mediaWindow>
    </main>
</template>
<script>
import fieldCard from './components/fieldCard.vue'
import growler from './components/growler.vue'
import alert from './components/alert.vue'
import mediaWindow from './components/mediaWindow.vue'
import createTemplate from './views/createTemplate.vue'
import loggedOut from './components/loggedOut.vue'
import createPage from './views/createPage.vue'
import router from '../js/admin.js'
import axios from 'axios'
import Bus from '../js/admin.js'

export default {
    name: 'App',
    data() {
        return {
            fields: [],
            logInError: false,
            growlerMessage: null,
            growlerMode: null,
            user: {},
            alertData: {}
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

        alert(alertData) {
            let alert = document.querySelector('.alert')

            this.alertData = alertData

            setTimeout(() => {
                alert.classList.add('show')
            }, 100)
        },
        toggleNav() {

            let nav = document.querySelector('.dashboard__nav');

            if (nav.classList.contains('open')) {
                return nav.classList.remove('open')
            }

            return nav.classList.add('open');
        },
        getApiToken() {
            axios.get('/user')
                .then((res) => {
                    this.api_token = res.data.api_token
                })
        }
    },
    components: {
        fieldCard,
        createTemplate,
        createPage,
        router,
        loggedOut,
        growler,
        alert,
        mediaWindow
    },
    beforeMount() {
        this.getApiToken();
    },
    mounted() {
        Bus.$on('growl', (growler) => {
            this.growl(growler)
        })

        Bus.$on('create', (data) => {
            axios.post(`/${data.type}?api_token=${this.api_token}`, data.data)
                .then((res) => {
                    let growlerData = {
                        mode: res.data.status,
                        message: res.data.message
                    }

                    if (data.redirect) {
                        this.$router.push({ name: data.redirect })
                    }

                    Bus.$emit('growl', growlerData);
                })
        })

        Bus.$on('update', (data) => {
            axios.patch(`/${data.type}?${data.type}_id=${data.id}&api_token=${this.api_token}`, data.data)
                .then((res) => {
                    let growlerData = {
                        mode: res.data.status,
                        message: res.data.message
                    }

                    if (data.redirect) {
                        this.$router.push({ name: data.redirect })
                    }

                    Bus.$emit('growl', growlerData);
                })
        })

        Bus.$on('getUser', (userId) => {
            axios.get(`/user?user_id=${userId}&api_token${this.api_token}`)
                .then((res) => {
                    Bus.$emit('returnUser', res.data)
                })
        })

        Bus.$on('alert', data => {
            this.alert(data);
        })

        Bus.$on('openMedia', (fieldId = null) => {
            let mw = document.querySelector('.media-window')

            mw.dataset.fieldId = fieldId;

            mw.classList.add('open')
        })

        Bus.$on('uploadMedia', (file) => {
            let formData = new FormData()
            formData.append('file', file)

            axios.post('/media', formData)
                .then((r) => {
                    if (r.status === 200) {
                        Bus.$emit('mediaUploaded')
                    }
                })
        })

        Bus.$on('deleteTarget', (targetData) => {
            axios.delete(`/${targetData.type}?${targetData.type}_id=${targetData.id}&api_token=${this.api_token}`)
                .then((res) => {
                    Bus.$emit('deleted', targetData.type)
                })
        })

        Bus.$on('getNewApiToken', (data) => {
            axios.post(`/token?user_id=${data.id}&api_token=${this.api_token}`)
                .then((res) => {
                    let growlerData = {
                        mode: res.data.status,
                        message: res.data.message
                    }

                    Bus.$emit('newTokenGenerated', data.id)
                    Bus.$emit('growl', growlerData)

                    this.getApiToken();
                })
        })

        Bus.$on('updateUser', (userData) => {
            axios.patch(`/user?user_id=${userData.user_id}&api_token=${this.api_token}`, userData)
                .then((res) => {
                    let growlerData = {
                        mode: res.data.status,
                        message: res.data.message
                    }

                    Bus.$emit('growl', growlerData)
                    this.$router.push({ name: 'dashboard' })

                    window.setTimeout(() => {
                        window.location.href = "/logout"
                    }, 3000)
                })
        })
    }
}

</script>
<style lang="css">
</style>
