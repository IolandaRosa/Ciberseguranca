<template>
    <div>
        <show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

        <div class="jumbotron">
            <h1>Logout</h1>
        </div>
        
        <div>
            <div class="form-group">
                <a class="btn btn-primary" v-on:click.prevent="logout">Logout</a>
            </div>
        </div>
        
    </div>
</template>

<script type="text/javascript">
    /*jshint esversion: 6 */  

    import showMessage from './helpers/showMessage.vue';

    export default {
        data: function(){
            return { 
                typeofmsg: "alert-success",
                showMessage: false,
                message: ""
            };
        },
        methods: {
            logout() {
                this.showMessage = false;
                axios.post('api/logout')
                .then(response => {
                    this.$socket.emit('user_exit', this.$store.state.user);
                    this.$store.commit('clearUserAndToken');
                    this.typeofmsg = "alert-success";
                    this.message = "User has logged out correctly";
                    this.showMessage = true;
                    this.$router.push({ path:'/items' });
                })
                .catch(error => {
                    this.$store.commit('clearUserAndToken');
                    this.typeofmsg = "alert-danger";
                    this.message = "Logout incorrect. But local credentials were discarded";
                    this.showMessage = true;
                });            
            },
            close(){
                this.showMessage=false;
            }
        },
        components: {
            'show-message':showMessage,
        }
    };
</script>