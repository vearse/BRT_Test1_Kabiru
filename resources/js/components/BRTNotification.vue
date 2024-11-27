
<template>
    <div class="notification" :class="notificationType">
        <p>{{ message }}</p>
    </div>
</template>

<script>
export default {
    props: {
        notificationType: {
            type: String,
            required: true
        },
        message: {
            type: String,
            required: true
        }
    }
}
</script>
