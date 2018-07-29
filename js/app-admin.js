var bmw_parser_Admin = new Vue({
	el: '#bmw_parser-admin',

	data: {
		url: '',
		result: '',
	},

	mounted: function() {

	},

	methods: {
		sendUrl() {
			var form_data = new FormData
			form_data.append('url', this.url)
			axios.post(ajaxurl + '?action=parse_run', form_data)
				.then(function (response) {
					console.log(response);
					this.result = 'Готово'
				}.bind(this))
				.catch(function (error) {
					console.log(error);
				});
		},

		getConnectionsList() {
			axios.get(ajaxurl, {params: {action: 'get_connections_list'}})
				.then(function (response) {
					this.connectionsList = response.data
				}.bind(this))
				.catch(function (error) {
					console.log(error)
				})
		},

		updateConnectionsList() {
			setInterval(function() {
				axios.get(ajaxurl, {params: {action: 'get_connections_list'}})
					.then(function (response) {
						this.connectionsList = response.data
					}.bind(this))
					.catch(function (error) {
						console.log(error)
					})
			}.bind(this), 3000)
		},

		getChatMessages(chat_id) {
			axios.get(ajaxurl, {params: {action: 'admin_get_messages_list', chat_id: chat_id}})
				.then(function (response) {
					this.messagesList = response.data
					this.currentChatId = chat_id
				}.bind(this))
				.catch(function (error) {
					console.log(error)
				})
		},

		updateChatMessages() {
			setInterval(function() {
				if ( this.currentChatId != null ) {
					axios.get(ajaxurl, {params: {action: 'admin_get_messages_list', chat_id: this.currentChatId}})
						.then(function (response) {
							this.messagesList = response.data
						}.bind(this))
						.catch(function (error) {
							console.log(error)
						})
				}
			}.bind(this), 3000)
		},

		scrollToBottom() {
			setTimeout(function() {
				var container = this.$el.querySelector('.messages-list')
				container.scrollTop = container.scrollHeight
			}.bind(this), 500)
		},

		shortString(str) {
			return (str.length >= 55) ? str.substr(0, 55) + '...' : str
		},

		timestampToDate(sec) {
		    var t = new Date(1970, 0, 1)
		    t.setSeconds(sec)
		    let year = t.getFullYear()
		    let month = (t.getMonth()+1) < 10 ? '0'+(t.getMonth()+1) : (t.getMonth()+1)
		    let day = t.getDate() < 10 ? '0'+t.getDate() : t.getDate()
		    let hours = t.getHours()
		    let minutes = t.getMinutes()
		    let seconds = t.getSeconds()
		    let date = hours +':'+ minutes// +' '+ day +'.'+ month +'.'+ year
		    return date
		},
	},
})