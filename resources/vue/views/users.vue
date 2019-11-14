<template>
    <section class="page">
        <h1>View Users</h1>
        <div v-for="user in users" class="card">
            <div class="card__details">
                <h3 class="user-card__name">{{ user.name }}</h3>
                <p class="user-card__info" v-if="superuser">Superuser</p>
                <p class="user-card__info">Created: {{ user.created_at }}</p>
                <p class="user-card__info">User ID: {{ user.user_id }}</p>
                <p class="user-card__info">API Token: {{ user.api_token }}</p>
            </div>
            <div class="card__utilities">
                <router-link tag="span" :to="'/admin/edit/user/' + user.user_id"><a>Edit User</a></router-link>
                <button @click="alert(user)" class="delete">Delete User</button>
            </div>
        </div>
    </section>
</template>
<script>
import axios from 'axios'
import router from '../../js/admin.js'
import Bus from '../../js/admin.js'

export default {

    name: 'viewUsers',

    data() {
        return {
            api_token: null,
            user_id: null,
            users: null
        };
    },
    methods: {
        deleteUser(user_id) {
            Bus.$emit('deleteUser', user_id);
        },
        alert(userData) {
            let data = {}

            if (this.api_token === userData.api_token && this.users.length <= 1) {
                data.intent = "delete"
                data.type = "alert"
                data.id = userData.user_id
                data.msg = "You cannot delete your own account if you are the only user."

                return Bus.$emit('alert', data)
            }

            data.type = 'user'
            data.method = "deleteTarget"
            data.id = this.user_id
            data.msg = 'WARNING: This will delete the user and remove any reference to them from the site. You may experience issues with any page that references them.'

            Bus.$emit('alert', data)
        },
        getUsers() {
            axios.get('/user')
                .then((res) => {
                    this.api_token = res.data.api_token
                    this.superuser = res.data.superuser
                    this.user_id = res.data.user_id
                }).then(() => {
                    axios.get(`/users?api_token=${this.api_token}`)
                        .then((res) => {
                            this.users = res.data
                        })
                }).catch(err => {
                    console.log(err)
                })
        }
    },
    beforeCreate() {

    },
    mounted() {
        Bus.$on('deleted', (type) => {
            if (type === 'user') {
                this.getUsers()
            }
        })

        this.getUsers()
    }
};

</script>
<style lang="css" scoped>
</style>
