<template>
    <div>   
        <div class="jumbotron">
            <h1>Login</h1>
        </div>
        <show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>
        <div>
            <div v-if="this.$route.params.isEmail==='true'" class="form-group">
                <label for="inputEmail">Email</label>
                <input
                type="email" class="form-control" v-model.trim="user.email"
                name="email" id="inputEmail"
                placeholder="Email address"/>
            </div>
            <div v-else class="form-group">
                <label for="username">Username</label>
                <input
                type="text" class="form-control" v-model.trim="user.username"
                name="username" id="username"
                placeholder="Username"/>
            </div>
            <div class="form-group">
                <label for="inputPassword">Password</label>
                <input
                type="password" class="form-control" v-model="user.password"
                name="password" id="inputPassword"/>
            </div>
            <div class="form-group">
                <a class="btn btn-primary" v-on:click.prevent="login">Login</a>
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
                user: {
                    email:"",
                    password:""
                },
                typeofmsg: "alert-success",
                showMessage: false,
                message: "",
            };
        },
        methods: {
            login() {
                this.showMessage = false;
                const formData = new FormData();
                let address='';
                if(this.$route.params.isEmail==='true'){
                    address='api/login';
                }
                else{
                    address='api/loginUsername';
                }
                axios.post('api/user/blockedOrNot/'+this.$route.params.isEmail, this.user)
                .then(response=>{
                    if(response.data.data.length==0){
                        this.typeofmsg = "alert-danger";
                        this.message = "User not found";
                        this.showMessage = true;
                        return;
                    }
                    if(response.data.data[0].blocked == 1)
                    {
                        this.typeofmsg = "alert-danger";
                        this.message = "User is blocked";
                        this.showMessage = true;
                    } else if (response.data.data[0].email_verified_at == null) {
                        this.typeofmsg = "alert-danger";
                        this.message = "Please, verify your email.";
                        this.showMessage = true;
                    } else
                    {
                        axios.post(address, this.user)
                        .then(response => {
                            console.log("set token ");
                            this.$store.commit('setToken',response.data.access_token);
                            return axios.get('api/users/me');
                        })
                        .then(response => {
                            this.$store.commit('setUser',response.data.data);
                            this.$socket.emit('user_enter', response.data.data);
                            this.typeofmsg = "alert-success";
                            this.message = "User authenticated correctly";
                            this.showMessage = true;
                            this.$router.push({ path:'/items' });
                        })
                        .catch(error => {
                            this.$store.commit('clearUserAndToken');
                            this.typeofmsg = "alert-danger";
                            this.message = "Invalid credentials";
                            this.showMessage = true;
                        });
                    }

                }).catch(error=>{
                    if(error.response.status==401){
                        this.showMessage=true;
                        this.message=error.response.data.unauthorized;
                        this.typeofmsg= "alert-danger";
                        return;
                    }
                });
            },
            close(){
                this.showMessage = false;
            }
        },
        components: {
            'show-message':showMessage,
        },  
    };
</script>

