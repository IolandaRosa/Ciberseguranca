<template>
    <div>
        <show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

        <div class="container">
          <div class="row">
            <div class="col">
                <div class="text-center">
                    <p class="h5"><strong>Unsigned Orders</strong></p>
                </div>
                <orders-list :orders="unsignedOrders" :isAll="true" :isAssignTocook="false" @assing-orders-get="getMyOrders" @unsigned-orders-get="getUnsignedOrders" :isWaiter="false" v-if="this.$store.state.user.type=='cook'"></orders-list>
            </div>
            <div class="col">
                <div class="text-center">
                    <p class="h5"><strong>My Orders</strong></p>
                </div>
                <orders-list :orders="orders" :isAll="true" :isAssignTocook="true" :isWaiter="false" @assing-orders-get="getMyOrders" @unsigned-orders-get="getUnsignedOrders" v-if="this.$store.state.user.type=='cook'"></orders-list>
            </div>
        </div>
    </div>


</div>
</template>

<script type="text/javascript">
    /*jshint esversion: 6 */

    import cookOrdersList from './cookOrdersList.vue';
    import showMessage from '../../helpers/showMessage.vue';

    export default {
        data: 
        function() {
            return {
                orders: [],
                unsignedOrders:[],
                showMessage:false,
                message:'',
                typeofmsg: "",
            };
        },
        methods: {
            getUnsignedOrders(){
                axios.get('api/unsignedOrders/')
                .then(
                    response=>{
                        this.unsignedOrders = response.data.data;
                    }).catch(error=>{
                        if(error.response.status==401){
                            this.showMessage=true;
                            this.message=error.response.data.unauthorized;
                            this.typeofmsg= "alert-danger";
                            return;
                        }
                    });
                },

                getMyOrders: function() {
                    axios.get('api/users/orders/'+this.$store.state.user.id)
                    .then(
                        response=>{
                            this.orders = response.data.data;
                        }).catch(error=>{
                            if(error.response.status==401){
                                this.showMessage = true;
                                this.message = error.response.data.unauthorized;
                                this.typeofmsg = "alert-danger";
                                return;
                            }
                        });
                    },
                    close(){
                        this.showMessage=false;
                    },
                },
                mounted() {

                    if(this.$store.state.user==null){
                        this.$router.push({ path:'/login' });
                        return;
                    }

                    this.getMyOrders();
                    this.getUnsignedOrders();
                },
                sockets:{
                  inform_alterations_unsigned_orders(){
                    this.getUnsignedOrders();
                },
                meal_terminated(){
                //console.log('allMeals create a new meal');
                this.getMyOrders();
                this.getUnsignedOrders();
            },
        },
        components: {
            'orders-list':cookOrdersList,
            'show-message':showMessage,
        },
    };
</script>

