import './bootstrap';
import BRTNotification from './components/BRTNotification.vue';

new Vue({
    el: '#app',
    components: {
        BRTNotification
    },
    data() {
        return {
            notifications: []
        }
    },
    mounted() {
        Echo.channel('BRT_Notification')
            .listen('BlumeReserveTicketCreated', (e) => {
                console.log( `New BRT created: ${e.brt.brt_code}`);
                this.addNotification('success', `New BRT created: ${e.brt.brt_code}`);
            })
            .listen('BlumeReserveTicketUpdated', (e) => {
                console.log( `BRT updated: ${e.brt.brt_code}`);
                this.addNotification('info', `BRT updated: ${e.brt.brt_code}`);
            })
            .listen('BlumeReserveTicketDeleted', (e) => {
                console.log( `BRT Deleedt: ${e.brt.brt_code}`);
                this.addNotification('error', `BRT deleted: ${e.brt.brt_code}`);
            });
    },
    methods: {
        addNotification(type, message) {
            this.notifications.push({ type, message });
        }
    }
});
