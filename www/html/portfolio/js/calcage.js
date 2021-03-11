function calcAge(birthdate) {
	        var today = new Date();
	        targetdate = today.getFullYear() * 10000 + (today.getMonth() + 1) * 100 + today.getDate();
	        return (Math.floor((targetdate - birthdate) / 10000));
}