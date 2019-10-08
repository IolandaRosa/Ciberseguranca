<template>

    <div>
        <vue-good-table ref="table"  :columns="columns" :rows="items" :pagination-options="{ enabled: true, perPage: 10}"
        :search-options="{ enabled: true}">
        <template slot="table-row" slot-scope="props">


        </template>
        </vue-good-table>
    </div>

</template>

<script>

    export default {
        props: ['items'],
        data:
            function() {
                return {
                    columns: [
                        {
                            label: "Item Id",
                            field: 'id',
                        }, {
                            label: 'Name',
                            field: 'name',
                        }, {
                            label: 'Type',
                            field: 'type',
                        }, {
                            label: 'Quantity',
                            field: 'quantity',
                        }, {
                            label: 'Unit Price',
                            field: 'unit_price',
                        }, {
                            label: 'Sub Total price',
                            field: 'sub_total_price',
                        },
                    ],
                };
            }
    };
</script>
