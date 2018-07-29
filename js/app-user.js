var chatsUser = new Vue({
	el: '#chats-user',

	data: {
		chatUserToken: null,
		chatWindowOpen: true,
		message: '',
		messageList: [],
	},

	mounted: function() {
		this.getToken()
		this.getMessagesList()
		this.updateMessageList()
		this.scrollToBottom()
	},

	methods: {
		getToken() {
			if ( this.chatUserToken === null ) {
				if ( !localStorage.getItem('token') ) {
					axios.get(chats_ajax.url, {params: {action: 'generate_token'}})
						.then(function (response) {
							this.chatUserToken = response.data
							localStorage.setItem('token', response.data)
						}.bind(this))
						.catch(function (error) {
							console.log(error)
						})
				}
				else {
					this.chatUserToken = localStorage.getItem('token')
				}
			}
		},

		sendMessage() {
			this.getToken()

			var form_data = new FormData;
			form_data.append('token', this.chatUserToken)
			form_data.append('message', this.messageProcessing(this.message))
			axios.post(chats_ajax.url + '?action=save_user_message', form_data)
				.then(function (response) {
					this.getMessagesList()
					this.message = ''
					this.scrollToBottom()
				}.bind(this))
				.catch(function (error) {
					console.log(error)
				})
		},

		getMessagesList() {
			axios.get(chats_ajax.url, {params: {action: 'get_messages_list', token: this.chatUserToken}})
				.then(function (response) {
					this.messageList = response.data
				}.bind(this))
				.catch(function (error) {
					console.log(error)
				})
		},

		updateMessageList() {
			setInterval(function() {
				axios.get(chats_ajax.url, {params: {action: 'get_messages_list', token: this.chatUserToken}})
					.then(function (response) {
						this.messageList = response.data
					}.bind(this))
					.catch(function (error) {
						console.log(error)
					})
			}.bind(this), 3000)
		},

		scrollToBottom() {
			setTimeout(function() {
				var container = this.$el.querySelector('.messages-list')
				container.scrollTop = container.scrollHeight
			}.bind(this), 500)
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

		messageProcessing(message) {
			message = message.replace(/(?:(?:https?|ftp):\/\/|\b(?:[a-z\d]+\.))(?:(?:[^\s()<>]+|\((?:[^\s()<>]+|(?:\([^\s()<>]+\)))?\))+(?:\((?:[^\s()<>]+|(?:\(?:[^\s()<>]+\)))?\)|[^\s`!()\[\]{};:'".,<>?«»“”‘’]))?/ig, '<a href="$&">$&</a>')
			message = message.replace(/(\n|\r\n|\r)/g, '<br>')
			return message
		},
	},
})