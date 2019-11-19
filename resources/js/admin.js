import Router from 'vue-router'
import App from '../vue/App.vue'

// import views
import dashboard from '../vue/views/dashboard.vue'
import createTemplate from '../vue/views/createTemplate.vue'
import createPage from '../vue/views/createPage.vue'
import viewPages from '../vue/views/pages.vue'
import viewTemplates from '../vue/views/templates.vue'
import editPage from '../vue/views/editPage.vue'
import editTemplate from '../vue/views/editTemplate.vue'
import viewUsers from '../vue/views/users.vue'
import viewMedia from '../vue/views/viewMedia.vue'
import editUser from '../vue/views/editUser.vue'
import viewTags from '../vue/views/tags.vue'


const Vue = window.Vue = require('vue')

// import components

const Bus = new Vue()

export default Bus

Vue.use(Router)
Vue.use(require('vue-moment'));
Vue.config.ignoredElements = ['trix-editor']

const router = new Router({
    mode: 'history',
    routes: [
        { path: '/admin', name: 'admin', component: dashboard },
        { path: '/admin/create/template', name: 'createTemplate', component: createTemplate },
        { path: '/admin/create/page/:parent_id?', name: 'createPage', component: createPage },
        { path: '/admin/view/pages', name: 'viewPages', component: viewPages },
        { path: '/admin/view/templates', name: 'viewTemplates', component: viewTemplates },
        { path: '/admin/edit/page/:page_id', name: 'editPages', component: editPage },
        { path: '/admin/edit/template/:template_id', name: 'editTemplates', component: editTemplate },
        { path: '/admin/edit/user/:user_id', name: 'editUser', component: editUser },
        { path: '/admin/view/users', name: 'viewUsers', component: viewUsers },
        { path: '/admin/view/media', name: 'viewMedia', component: viewMedia },
        { path: '/admin/view/tags', name: 'viewTags', component: viewTags }
    ]
})

new Vue({
    data: {},
    router,
    components: { App },
    template: '<App/>',
    mounted() {}
}).$mount('#app')
