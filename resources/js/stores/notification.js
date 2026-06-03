import { ref } from 'vue';
import { defineStore } from 'pinia';
import { useToast } from 'vue-toastification';

export const useNotificationStore = defineStore('notification', () => {
    const notifications = ref([]);
    const toast = useToast();

    let nextId = 0;

    function addNotification(type, msg) {
        const id = ++nextId;
        notifications.value.push({ id, type, message: msg });
        toast[type](msg);
        return id;
    }

    function success(msg) {
        return addNotification('success', msg);
    }

    function error(msg) {
        return addNotification('error', msg);
    }

    function info(msg) {
        return addNotification('info', msg);
    }

    function remove(id) {
        notifications.value = notifications.value.filter((n) => n.id !== id);
    }

    return {
        notifications,
        success,
        error,
        info,
        remove,
    };
});
