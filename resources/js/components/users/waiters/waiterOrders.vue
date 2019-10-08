<template>
    <div >
        <div class="jumbotron">
            <h1>{{title}}</h1>
        </div>

        <div class="inline-buttons">
            <a class="btn btn-info" v-on:click.prevent="getOrders">Pending/Confirmed Orders</a>

            <a class="btn btn-success" v-on:click.prevent="getPreparedOrders">Prepared Orders</a>

            <a class="btn btn-danger" v-on:click.prevent="getSeasoningOrders">Orders Seasoning</a>

            <a class="btn btn-primary" v-on:click.prevent="getUnSeasoningOrders">Orders Without Seasoning</a>
        </div>


        <div class="jumbotron">

            <div class="form-group">

                <orders-list :orders="orders" :isAll="true"  :isWaiter="true" v-if="this.$store.state.user.type=='waiter'" @cancel-click="cancelOrder" @refresh-prepared-orders="getPreparedOrders"
                ></orders-list>

            </div>

            <div class="form-group">
                <a class="btn btn-primary" v-on:click.prevent="createOrder">Create Order</a>
            </div>
        </div>

    </div>
</template>



<script type="text/javascript">
    /*jshint esversion: 6 */

    import errorValidation from '../../helpers/showErrors.vue';
    import showMessage from '../../helpers/showMessage.vue';
    import cookOrdersList from '../cooks/cookOrdersList.vue';

    export default{
        props:['orderId','refresh5Seconds'],
        data() {
            return {
                showMessage: false,
                message: "",
                errors: [],
                showErrors: false,
                typeofmsg: "",
                user: this.$store.state.user,
                orders: [],
                timer: '',
                title: '',
            };

        },
        sockets:{
            refresh_orders(dataFromServer){
                this.getOrders();
            },
            refresh_prepared_orders(dataFromServer){
                this.getPreparedOrders();
            },

        },
        methods:{
            getSeasoningOrders(){
                this.title = 'Seasoning Orders';
                axios.get('api/user/myOrdersWaiterSeasoning/'+this.user.id)
                .then(response=>{
                    this.orders = response.data.data;
                }).catch(error=>{
                    console.log(error.response);
                });
            },
            getUnSeasoningOrders(){
                this.title = 'Without Seasoning Orders';
                axios.get('api/user/myOrdersWaiterUnseasoning/'+this.user.id)
                .then(response=>{
                    this.orders = response.data.data;
                }).catch(error=>{
                    console.log(error.response);
                });
            },
            getOrders: function() {
                this.title = 'Pending/Confirmed Orders';
                axios.get('api/user/myOrdersWaiter/'+this.user.id)
                .then(response=>{
                    this.orders = response.data.data;
                });

            },
            close(){
                this.showErrors=false;
                this.showMessage=false;
            },
            changeStateToConfirmed: function() {
                axios.patch('api/orders/state/'+this.orderId,
                {
                    state:'confirmed',
                }).
                then(response=>{
                    this.getOrders();

                    axios.get('api/meals/mealFromOrder/'+this.orderId)
                    .then(response=>{
                        this.$socket.emit('inform-orders-meal-summary', this.$store.state.user.id,response.data.data[0].meal_id);
                        this.$socket.emit('inform-new-order', response.data.data[0].meal_id);
                    });

                    this.$socket.emit('waiter-inform-cooks-new-order', this.$store.state.user);
                }).
                catch(error=>{
                    if(error.response.status==422){
                        this.showMessage=true;
                        this.message=error.response.data.error;
                        this.typeofmsg= "alert-danger";
                    }
                });

                clearInterval(this.timer);
            }, 
            getPreparedOrders: function() {
                this.title = 'Prepared Orders';
                axios.get('api/user/myPreparedOrdersWaiter/'+this.user.id)
                .then(response=>{
                    this.orders = response.data.data;
                });

            },
            cancelOrder(id){

                axios.delete('api/orders/'+id).
                then(response=>{
                    this.getOrders();
                }).
                catch(error=>{
                    if(error.response.status==422){
                        this.showMessage=true;
                        this.message=error.response.data.error;
                        this.typeofmsg= "alert-danger";
                    }
                });
            },
            createOrder(){
                this.$router.push({ path:'/newOrder' });
            },
        },
        mounted(){
            this.getOrders();
            this.title = 'Pending/Confirmed Orders';
            if(this.refresh5Seconds == true)
            {
                this.timer = setInterval(this.changeStateToConfirmed,5000);
            }
        },
        components: {
            'error-validation': errorValidation,
            'show-message': showMessage,
            'orders-list': cookOrdersList,
        }
    };
</script>

<style scoped>

.inline-buttons .one-third {
    text-align: center;
}

@media only screen and (max-width: 1076px) {

    .inline-buttons .one-third {
        width: 100%;
        margin: 20px;
    }
}
</style>
