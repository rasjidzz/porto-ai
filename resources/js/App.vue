<template>
    <div>
        <h1>Laravel + Vue Integration</h1>
        <button @click="showAlert">Click Me</button>
    </div>
</template>

<script>
export default {
    methods: {
        showAlert() {
            alert("Vue is working in Laravel!");
        }
    }
}
</script>