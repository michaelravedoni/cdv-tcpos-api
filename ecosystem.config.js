module.exports = {
  apps : [{
    name   : "cdv-tcpos-api-queue",
    script : "artisan",
    interpreter : "php",
    instances: 1,
    args : "queue:work"
  }]
}
