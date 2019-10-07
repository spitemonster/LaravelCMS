<template>
    <section class="view__users">
        <h1>View Users</h1>
        <div v-for="user in users" :class="[user.api_token === api_token ? 'fart' : '']">
            <h3>{{ user.name }}</h3>
            <p v-if="superuser">Superuser</p>
            <p>Created: {{ user.created_at }}</p>
            <p>User ID: {{ user.user_id }}</p>
            <p>API Token: {{ user.api_token }}</p>
            <template v-if="user.api_token === api_token || superuser">
                <button @click="deleteUser(user.user_id)" class="delete">Delete User</button>
            </template>
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
        }
    },
    beforeCreate() {
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
            })
    },
    mounted() {
        Bus.$on('userDeleted', (users) => {
            if (users) {
                this.users = users
            }
        })
    }
};

</script>
<style lang="css" scoped>
</style>
