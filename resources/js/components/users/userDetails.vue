<template>
    <div>
        <div v-if="isNotManager" class="jumbotron">
            <h1>Profile</h1>
        </div>
        <show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

        <error-validation :showErrors="showErrors" :errors="errors" @close="close"></error-validation>

        <div>
            <img :src="'storage/profiles/'+user.photo_url" alt="Item Photo" width="130" height="150" class="rounded mx-auto d-block">
            <div class="form-group">
                <label for="username" class="col-sm-4 col-form-label">User Name</label>
                <div class="col-sm-10">
                    <input type="text" name="username" class="form-control" id="username" v-model="user.username"/>
                </div>
            </div>
            <div class="form-group">
                <label for="fullName" class="col-sm-4 col-form-label">Full name</label>
                <div class="col-sm-10">
                    <input type="text" name="fullName" class="form-control" id="fullName" v-model="user.name"/>
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail" class="col-sm-4 col-form-label">Email</label>
                <div v-if="isNotManager" class="col-sm-10">
                    <input class="form-control" type="email" v-model="user.email" name="email" id="inputEmail" placeholder="Email address" readonly/>
                </div>
                <div v-else class="col-sm-10">
                    <input class="form-control" type="email" v-model="user.email" name="email" id="inputEmail" placeholder="Email address"/>

                </div>
            </div>
            <div class="col-sm-10">
                <file-upload v-on:fileChanged="onFileChanged"> </file-upload>
                <br>
                <div class="text-right">
                    <a class="btn btn-outline-primary btn-xs" @click.prevent="updateUser">Save Changes</a>
                    <div v-if="!isNotManager">
                        <button class="btn btn-outline-danger btn-xs" @click.prevent="cancelEditUser">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script type="text/javascript">
    /*jshint esversion: 6 */

    import errorValidation from '../helpers/showErrors.vue';
    import showMessage from '../helpers/showMessage.vue';
    import fileUpload from '../helpers/uploadFile.vue';

    export default{
        props:['userToUpdate','isManagerProfile'],
        data() {
            return {
                errors: [],
                showMessage: false,
                showErrors: false,
                user: this.$store.state.user,
                typeofmsg: "",
                message:'',
                file: '',
                isNotManager: true,
            };
        },
        methods:{
            onFileChanged(fileSelected) {
                this.file = fileSelected
            },
            cancelEditUser() {
                this.$emit('cancel-user-click');
            },
            updateUser(){
                this.showMessage=false;
                this.showErrors=false;

                const formData = new FormData();

                if(this.file != null)
                {
                    formData.append('photo', this.file);
                }
                if(!this.isNotManager)
                {
                    formData.append('email', this.user.email);
                }
                formData.append('name', this.user.name);
                formData.append('username', this.user.username);
                formData.append('_method', 'put');

                axios.post('api/users/update/'+this.user.id, formData).then(response => {
                    this.user.photo_url = response.data.data.photo_url;
                    this.showErrors=false;
                    this.showMessage=true;
                    this.message='Profile updated with success';
                    this.typeofmsg= "alert-success";

                    if(this.isNotManager)
                    {
                        this.$store.commit("setUser", response.data.data);
                        this.$router.push({ path:'/items' });
                    }else
                    {
                          this.$emit('user-changed-click');
                    }
                }).
                catch(error=>{
                    if(error.response.status==401){
                        this.showMessage=true;
                        this.message=error.response.data.unauthorized;
                        this.typeofmsg= "alert-danger";
                        return;
                    }

                    if(error.response.status==422){
                        if(error.response.data.errors==undefined){
                            this.showErrors=false;
                            this.showMessage=true;
                            this.message=error.response.data.user_already_exists;
                            this.typeofmsg= "alert-danger";
                        }else{
                            this.showMessage=false;
                            this.showErrors=true;
                            this.errors=error.response.data.errors;
                        }
                    }
                });
            },
            close(){
                this.showErrors=false;
                this.showMessage=false;
            },
        },
        mounted(){
            if(this.$store.state.user==null){
                this.$router.push({ path:'/login' });
                return;
            }
            if(this.userToUpdate != null)
            {
                this.user = this.userToUpdate;
            }

            if(this.isManagerProfile != null)
            {
                this.isNotManager = this.isManagerProfile;
            }
        },
        components: {
            'error-validation':errorValidation,
            'show-message':showMessage,
            'file-upload': fileUpload,
        },
    };
</script>