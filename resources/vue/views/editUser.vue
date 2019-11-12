<template>
    <div class="page">
        <template v-if="user">
            <fieldset>
                <input type="text" v-model="user.name" />
                <label>Name</label>
            </fieldset>
            <fieldset>
                <input type="text" v-model="user.email" />
                <label>Email</label>
            </fieldset>
            <div class="l-halves" v-if="api_token === user.api_token">
                <fieldset>
                    <input type="password" v-model="newPassword">
                    <label>New Password</label>
                </fieldset>
                <fieldset>
                    <input type="password" v-model="confirmPassword">
                    <label>Confirm Password</label>
                </fieldset>
            </div>
            <fieldset>
                <input type="text" v-model="user.user_id" readonly="readonly">
                <label>User ID</label>
            </fieldset>
            <fieldset>
                <input type="text" v-model="user.api_token" id="apiToken" readonly="readonly" />
                <label>API Token</label>
                <button class="delete" @click="alert(user.user_id)">Request New Token</button>
            </fieldset>
            <button class="btn btn-primary" @click="updateUser()">Save</button>
        </template>
    </div>
</template>
<script>
import Bus from '../../js/admin.js'
import router from '../../js/admin.js'
import axios from 'axios'

export default {
    name: '',
    data() {
        return {
            user: null,
            newPassword: null,
            confirmPassword: null,
            api_token: null
        }
    },
    props: [],
    methods: {
        alert(userId) {
            let alertData = {
                intent: 'get',
                type: 'API Token',
                id: userId,
                method: 'getNewApiToken',
                msg: 'WARNING: This will refresh your API token. Any applications or services you use with this token may experience errors until your new token is applied.'
            }

            Bus.$emit('alert', alertData)
        },
        updateUser() {
            let userData;
            if (this.newPassword === this.confirmPassword) {
                userData = this.user;

                userData.newPassword = this.newPassword;
                Bus.$emit('updateUser', userData);
            }
        },
        getSelf() {
            axios.get('/user')
                .then((res) => {
                    this.api_token = res.data.api_token
                })
        }
    },
    beforeCreate() {
        // let token = document.querySelector('#api_token');
        let targetUser = this.$route.params.user_id

        Bus.$emit('getUser', targetUser)

        Bus.$on('returnUser', (user) => {
            this.user = user
        })

    },
    mounted() {
        Bus.$on('newTokenGenerated', () => {
            Bus.$emit('getUser', this.$route.params.user_id)
            this.getSelf();
        })

        this.getSelf()
    }
}

</script>
<style lang="css">
.l-halves {
    max-width: 960px;
    justify-content: space-between;
}

.l-halves>* {
    width: 49%;
    flex-grow: 0;
    ;
}

.l-halves fieldset {
    margin-top: 0;
}

</style>
