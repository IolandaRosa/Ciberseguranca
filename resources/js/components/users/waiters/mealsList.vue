<template>
    <div>
        <div v-if="this.$store.state.user!=null">

            <show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

            <vue-good-table ref="table"  :columns="columns" :rows="meals" :pagination-options="{ enabled: true, perPage: 10}"
                            :search-options="{ enabled: true}" @on-row-click="onRowClick" :row-style-class="rowStyleFn">
                <template slot="table-row" slot-scope="props" class="in_prep">

                    <span v-if="props.column.field == 'state'">
                        {{props.row.state}}
                    </span>

                    <span v-if="props.column.field == 'table_number' ">
                            {{props.row.table_number}}
                    </span>

                    <span v-if="props.column.field == 'total_price_preview'">
                            {{props.row.total_price_preview}}
                    </span>

                    <span v-if="props.column.field == 'id'">
                            {{props.row.id}}
                    </span>

                    <span v-if="props.column.field == 'responsible_waiter_id'">
                            {{props.row.responsible_waiter_id}}
                    </span>

                    <span v-if="props.column.field == 'actions' && props.row.state=='active'  && terminate == true && isManager == false">
                            <button @click="terminateMeal(props.row.id)" class="btn btn-outline-info btn-xs" data-toggle="modal">Mark as terminated</button>
                    </span>

                    <span v-if="props.column.field == 'actions' && props.row.state=='terminated'  && terminate == true">
                            <button @click="markMealAsNotPaid(props.row.id)" class="btn btn-outline-danger btn-xs" data-toggle="modal">Mark as not paid</button>
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
        props:['meals','terminate','isManagerDashboard'],
        data:
            function() {
                return {
                    showMessage:false,
                    message:'',
                    typeofmsg: "",
                    selectedRow: null,
                    terminated: false,
                    isManager: false,
                    columns: [{
                            label: 'Meal',
                            field: 'id',
                            type: 'number',
                        },{
                            label: 'Table Number',
                            field: 'table_number',
                            type: 'number',
                        },{
                            label: 'Total price preview',
                            field: 'total_price_preview',
                        },
                        {
                            label: 'State',
                            field: 'state',
                        },
                        {
                            label: 'Responsible Waiter Id',
                            field: 'responsible_waiter_id',
                        },
                        {
                            label: 'Actions',
                            field: 'actions',
                            sortable: false,
                        }
                    ],

                };
            },
        methods:{
            onRowClick(params){
                this.selectedRow = params.row.originalIndex;
                let values = [this.selectedRow,this.terminated,false,params.row.id];
                this.$emit('selectedRow',values);
                this.terminated = false;
            },
            rowStyleFn(row) {
                return this.selectedRow === row.originalIndex ?'selectedRow':'';
            },terminateMeal(row) {
                this.terminated = true;
            },
            markMealAsNotPaid(row) {
                this.$emit("meal-not-paid", row);
            },
            close(){
                this.showMessage=false;
            }
        },
        mounted(){
            if(this.isManagerDashboard != null)
            {
                this.isManager = this.isManagerDashboard;
            }

            this.$set(this.columns[4], 'hidden', !this.isManager);
            this.$set(this.columns[5], 'hidden', !this.terminate);
        },
        components: {
            'show-message':showMessage,
        },

    };


</script>

<style >
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
    .prep{
        font-weight: bold;
        background: #FF8C00  !important;
        color: #fff          !important;
        padding: 0px 5px;
    }

    .selectedRow{
        background-color: darkgrey;
    }
</style>
