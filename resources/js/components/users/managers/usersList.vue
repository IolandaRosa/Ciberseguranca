<template>
	<div>
		<p class="h5"><strong>Users</strong></p>

		<vue-good-table :columns="columns" :rows="users" :pagination-options="{ enabled: true, perPage: 10}" :search-options="{ enabled: true}">
			<template slot="table-row" slot-scope="props">

				<span v-if="props.column.field == 'blocked' ">
					<span v-if="props.formattedRow[props.column.field]==0">
						No
					</span>
					<span v-else>
						Yes
					</span>
				</span>

				<span v-if="props.column.field != 'actions' && props.column.field != 'blocked' && props.column.field != 'photo_url'">
					{{props.formattedRow[props.column.field]}}
				</span>

				<span v-if="props.column.field == 'photo_url'" >
					<img :src="'storage/profiles/'+props.row.photo_url" alt="Item Photo" width="50" height="60">
				</span>

				<span v-if="props.column.field == 'actions' ">
					<button class="btn btn-outline-success btn-xs" @click="editUser(props.row.id)"><i class="far fa-edit"></i>Edit</button>

					<span v-if="props.row.id != logedUser.id">

						<span v-if="props.row.deleted_at  == null">
							<button class="btn btn-outline-danger btn-xs" @click="deleteUser(props.row.id)"><i class="fas fa-trash-alt"></i>Delete</button>
						</span>

						<span v-if="props.row.blocked == 0">
							<button class="btn btn btn-outline-dark btn-xs" @click="blockUser(props.row.id)">Block</button>
						</span>
						<span v-else>
							<button class="btn btn-outline-info btn-xs" @click="unBlockUser(props.row.id)">Unblock</button>
						</span>
					</span>

				</span>
			</template>
		</vue-good-table>

	</div>
</template>

<script type="text/javascript">
	/*jshint esversion: 6 */

	export default {
		props:['users'],
		data:
		function() {
			return {
				logedUser: this.$store.state.user,
				columns: [
				{
					label: 'Id',
					field: 'id',
					sortable:true,
				},
				{
					label: 'Name',
					field: 'name',
					sortable:true,
				},
				{
					label: 'Username',
					field: 'username',
					sortable:true,
				},{
					label: 'Email',
					field: 'email',
					sortable:true,
				},
				{
					label: 'Photo',
					field: 'photo_url',
				},
				{
					label: 'Type',
					field: 'type',
					sortable:true,
				},
				{
					label: 'Blocked',
					field: 'blocked',
					sortable:true,
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
			editUser(userId){
				this.$emit('user-edit', userId);
			},
			blockUser(userId){
				this.$emit('user-block', userId);
			},
			unBlockUser(userId){
				this.$emit('user-unblock', userId);
			},
			deleteUser(userId){
				this.$emit('user-delete', userId);
			}
		},mounted(){
			this.$set(this.columns[0], 'hidden', true);
		},
	};
</script>