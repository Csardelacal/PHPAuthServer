import { format } from "date-fns";

window.formatBanTime = function (){
    document.querySelectorAll('.ban-time').forEach(
        function(item) {
            console.log(item);
            item.innerText = format(parseInt(item.dataset.banTime, 10)*1000, 'MM/dd/yyyy hh:mm aaa'); // US
            item.title = format(parseInt(item.dataset.banTime, 10)*1000, 'yyyy-dd-MM HH:mm'); // ISO
        }
    );
}
