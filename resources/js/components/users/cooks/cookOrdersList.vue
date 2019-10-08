<template>
    <div>
        <div v-if="this.$store.state.user!=null">

            <show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

            <vue-good-table ref="table" :columns="columns" :rows="orders" :pagination-options="{ enabled: true, perPage: 10}" :search-options="{ enabled: true}">
                <template slot="table-row" slot-scope="props">

                    <span v-if="props.column.field == 'state' && props.row.state=='in preparation'">
                        <span class="in_prep">
                            {{props.row.state}}
                        </span>
                    </span>

                    <span v-if="props.column.field == 'state' && props.row.state=='confirmed'">
                        <span class="conf">{{props.row.state}}</span>
                    </span>

                    <span v-if="props.column.field == 'state' && props.row.state=='pending'">
                        <span class="pend">{{props.row.state}}</span>
                    </span>

                    <span v-if="props.column.field == 'state' && props.row.state=='delivered'">
                        <span class="del">{{props.row.state}}</span>
                    </span>

                    <span v-if="props.column.field == 'state' && props.row.state=='prepared'">
                        <span class="prep">{{props.row.state}}</span>
                    </span>

                    <span v-if="props.column.field == 'state' && props.row.state=='not delivered'">
                        <span class="ndel">{{props.row.state}}</span>
                    </span>

                    <span v-if="props.column.field=='actions' && props.row.state=='in preparation' && isWaiter ==false">
                        <button @click="updatePrepared(props.row.id)" class="btn btn-outline-success btn-xs">Mark as prepared</button>
                    </span>

                    <span v-if="props.column.field=='actions' && props.row.state=='confirmed' && isWaiter == false">
                        <span v-if="isAssignTocook==false">
                            <button @click="assingOrderToCook(props.row.id)" class="btn btn-outline-info btn-xs">Assing To Me</button>
                        </span>
                        <span v-else>
                            <button @click="updateInPreparation(props.row.id)" class="btn btn-outline-info btn-xs">Mark as in preparation</button>
                        </span>
                    </span>

                    <span v-if="props.column.field=='actions' && props.row.state=='pending' && isWaiter == true">
                        <button @click="cancelOrder(props.row.id)" class="btn btn-outline-danger btn-xs">Cancel order</button>
                    </span>


                    <span v-if="props.column.field=='actions' && props.row.state=='prepared' && isWaiter == true">
                        <button @click="updateDelivered(props.row.id)" class="btn btn-outline-info btn-xs">Mark as delivered</button>
                    </span>

                    <span v-if="props.column.field != 'state' && props.column.field != 'actions'">
                        {{props.formattedRow[props.column.field]}}
                    </span>
                </template>
            </vue-good-table>
        </div>
    </div>
</template>

<script type="text/javascript">
    /*jshint esversion: 6 */
    import showMessage from '../../helpers/showMessage.vue';

    export default {
        props:['orders','isAll','isAssignTocook','isWaiter','ordersSummary'],
        data:
            function() {
                return {
                    showMessage:false,
                    message:'',
                    typeofmsg: "",
                    columns: [
                        {
                            label: 'Id',
                            field: 'id',
                            sortable:false,
                        }, {
                            label: 'State',
                            field: 'state',
                        }, {
                            label: 'Item Id',
                            field: 'item_id',
                            sortable:false,
                        }, {
                            label: 'Item Name',
                            field: 'name',
                            sortable:false,
                        },{
                            label: 'Item Price',
                            field: 'price',
                            sortable:false,
                        }, {
                            label: 'Meal Id',
                            field: 'meal_id',
                            sortable:false,
                        }, {
                            label: 'Start Date',
                            field: 'start',
                            type: 'date',
                            dateInputFormat: 'YYYY-MM-DD HH:mm:ss',
                            dateOutputFormat: 'DD/MM/YYYY HH:mm:ss',
                        }, {
                            label:'Seasoning',
                            field:'seasoning',
                        }, {
                            label: 'Actions',
                            field: 'actions',
                            sortable: false,
                        }
                    ],

            };
        },
        methods:{
            updatePrepared(id){
                axios.patch('api/orders/state/'+id,
                {
                    state:'prepared',
                }).
                then(response=>{
                    this.$emit('assing-orders-get');
                    this.sendRefreshNotificationPreparedOrders(id);

                    axios.get('api/meals/mealFromOrder/'+id)
                        .then(response=>{
                            this.$socket.emit('inform-orders-meal-summary', response.data.data[0].responsible_waiter_id,response.data.data[0].meal_id);
                        });
                }).
                catch(error=>{
                    if(error.response.status==422){
                        this.showMessage=true;
                        this.message=error.response.data.error;
                        this.typeofmsg= "alert-danger";
                    }
                });

            },
            assingOrderToCook(orderId){
                axios.patch('api/orders/cooks/'+orderId,
                {
                    cook:this.$store.state.user.id
                }).
                then(response=>{
                    this.$emit('assing-orders-get');
                    this.$emit('unsigned-orders-get');
                    this.sendRefreshNotification(orderId);
                    this.$socket.emit('inform-cooks-assing-order', this.$store.state.user);

                    axios.get('api/meals/mealFromOrder/'+orderId)
                        .then(response=>{
                            this.$socket.emit('inform-orders-meal-summary', response.data.data[0].responsible_waiter_id,response.data.data[0].meal_id);
                        });
                }).
                catch(error=>{
                    if(error.response.status==422){
                        this.showMessage=true;
                        this.message=error.response.data.error;
                        this.typeofmsg= "alert-danger";
                    }
                });
            },
            updateInPreparation(id){
                axios.patch('api/orders/state/'+id,
                {
                    state:'in preparation',
                }).
                then(response=>{
                    this.$emit('assing-orders-get');

                    this.sendRefreshNotification(id);
                }).
                catch(error=>{
                    if(error.response.status==422){
                        this.showMessage=true;
                        this.message=error.response.data.error;
                        this.typeofmsg= "alert-danger";
                    }
                });

            }, updateDelivered(id){
                axios.patch('api/orders/state/'+id,
                {
                    state:'delivered',
                }).
                then(response=>{
                    this.$emit('refresh-prepared-orders');
                }).
                catch(error=>{
                    if(error.response.status==422){
                        this.showMessage=true;
                        this.message=error.response.data.error;
                        this.typeofmsg= "alert-danger";
                    }
                });

            },
            sendRefreshNotification(orderId, assignedToCook = false){
                axios.get('api/orders/responsibleWaiter/'+orderId).
                then(response=>{
                    if (assignedToCook) {
                        this.$socket.emit('refresh', this.$store.state.user, response.data.data[0].responsible_waiter_id, orderId, true);
                    } else {
                        this.$socket.emit('refresh', this.$store.state.user, response.data.data[0].responsible_waiter_id, orderId);
                    }
                }).
                catch(error=>{
                    if(error.response.status==422){
                        this.showMessage=true;
                        this.message=error.response.data.error;
                        this.typeofmsg= "alert-danger";
                    }
                });
            },
            sendRefreshNotificationPreparedOrders(orderId){
                axios.get('api/orders/responsibleWaiter/'+orderId).
                then(response=>{
                    this.$socket.emit('refreshPrepared', this.$store.state.user, response.data.data[0].responsible_waiter_id, orderId);
                }).
                catch(error=>{
                    if(error.response.status==422){
                        this.showMessage=true;
                        this.message=error.response.data.error;
                        this.typeofmsg= "alert-danger";
                    }
                });
            },
            cancelOrder(id){
                this.$emit('cancel-click', id);
            },
            close(){
                this.showMessage=false;
            }
        },
        mounted(){
            this.$set(this.columns[7], 'hidden', !this.isAll); //is this right? é esta?
            this.$set(this.columns[2], 'hidden', this.ordersSummary); //item_id
            this.$set(this.columns[5], 'hidden', this.ordersSummary); //meal_id
            this.$set(this.columns[7], 'hidden', this.ordersSummary); //actions
            this.$set(this.columns[3], 'hidden', !this.ordersSummary);
            this.$set(this.columns[4], 'hidden', !this.ordersSummary);
        },
        components: {
            'show-message':showMessage,
        },

    };
</script>

<style scoped>
.in_prep{
    font-weight: bold;
    background: green  !important;
    color: #fff          !important;
    padding: 0px 5px;
}

    .conf{
        font-weight: bold;
        background: #123456  !important;
        color: #fff          !important;
        padding: 0px 5px;
    }
    .pend{
        font-weight: bold;
        background: #ff2f36 !important;
        color: #fff          !important;
        padding: 0px 5px;
    }

    .prep{
        font-weight: bold;
        background: #ffb84c !important;
        color: #fff          !important;
        padding: 0px 5px;
    }

    .del{
        font-weight: bold;
        background: #ff5921 !important;
        color: #fff          !important;
        padding: 0px 5px;
    }

    .ndel{
    font-weight: bold;
    background: #813aff !important;
    color: #fff          !important;
    padding: 0px 5px;
    }
</style>
