<template>
	<div>
		<p class="h5"><strong>Tables</strong></p>

		<vue-good-table :columns="columns" :rows="tables" :pagination-options="{ enabled: true, perPage: 13}" :search-options="{ enabled: true}">
			<div slot="table-actions">
				<button class="btn btn-outline-info btn-xs" @click="createTable"><i class='fas fa-plus'>&nbsp;</i>New Table</button>
			</div>
			<template slot="table-row" slot-scope="props">
				<span v-if="props.column.field != 'actions'">
					{{props.formattedRow[props.column.field]}}
				</span> 
				<span v-else>
					<button class="btn btn-outline-success btn-xs" @click="editTable(props.row.table_number)"><i class="far fa-edit"></i>Edit</button>
					<button class="btn btn-outline-danger btn-xs" @click="deleteTable(props.row.table_number)"><i class="fas fa-trash-alt"></i>Delete</button>
				</span>
			</template>
		</vue-good-table>

	</div>
</template>

<script type="text/javascript">
	/*jshint esversion: 6 */

	export default {
		props:['tables'],
		data:
		function() {
			return {
				columns: [
					{
						label: 'Number',
						field: 'table_number',
						sortable:true,
						type: 'number',
					},
					{
						label: 'Actions',
						field: 'actions',
						sortable:false,
					}
				],
			};
		},
		methods:{
			createTable(){
				this.$emit('table-create');
			},
			editTable(tableNumber){
				this.$emit('table-edit', tableNumber);
			},
			deleteTable(tableNumber){
				this.$emit('table-delete', tableNumber);
			}
		},
	};
</script>