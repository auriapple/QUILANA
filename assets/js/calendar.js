let selectedDate = "";

const daysTag = document.querySelector(".days"),
currentDate = document.querySelector(".current-date"),
prevNextIcon = document.querySelectorAll(".icons span");

// getting new date, current year and month
let date = new Date(),
currYear = date.getFullYear(),
currMonth = date.getMonth();

// storing full name of all months in array
const months = ["January", "February", "March", "April", "May", "June", "July",
              "August", "September", "October", "November", "December"];

const renderCalendar = () => {
    let firstDayofMonth = new Date(currYear, currMonth, 1).getDay(), // getting first day of month
    lastDateofMonth = new Date(currYear, currMonth + 1, 0).getDate(), // getting last date of month
    lastDayofMonth = new Date(currYear, currMonth, lastDateofMonth).getDay(), // getting last day of month
    lastDateofLastMonth = new Date(currYear, currMonth, 0).getDate(); // getting last date of previous month
    let liTag = "";

    for (let i = firstDayofMonth; i > 0; i--) { // creating li of previous month last days
        liTag += `<li class="inactive">${lastDateofLastMonth - i + 1}</li>`;
    }

    for (let i = 1; i <= lastDateofMonth; i++) { // creating li of all days of current month
        // adding active class to li if the current day, month, and year matched
        let isToday = i === date.getDate() && currMonth === new Date().getMonth() 
                     && currYear === new Date().getFullYear() ? "today active" : "";
        liTag += `<li class="${isToday}">${i}</li>`;
    }

    for (let i = lastDayofMonth; i < 6; i++) { // creating li of next month first days
        liTag += `<li class="inactive">${i - lastDayofMonth + 1}</li>`
    }
    currentDate.innerText = `${months[currMonth]} ${currYear}`; // passing current mon and yr as currentDate text
    daysTag.innerHTML = liTag;

    daysTag.innerHTML = liTag;
    addClickEvents(); // Function to add click events

    const days = document.querySelectorAll(".days li");

    // Loop through each date and check if it has a scheduled assessment
    days.forEach(day => {
        const dayNumber = day.textContent;
        const isInactive = day.classList.contains('inactive'); // Check if the day is inactive
    
        // Skip adding dots for inactive days
        if (isInactive) return;

        const dateStr = `${currYear}-${(currMonth + 1).toString().padStart(2, '0')}-${dayNumber.padStart(2, '0')}`;
        
        if (scheduledDates.includes(dateStr)) {
            // Create a dot element to indicate a scheduled assessment
            const dot = document.createElement('span');
            dot.className = 'dot';
            day.appendChild(dot);
        }
    });
}

const addClickEvents = () => {
    const days = document.querySelectorAll(".days li");

    days.forEach(day => {
        day.addEventListener("click", () => {
            // Remove active class from all days except for today
            days.forEach(d => {
                d.classList.remove("active");
            });

            // Add active class to the clicked day
            day.classList.add("active");


            // Get the selected date
            const dayNumber = day.textContent;
            const month = currMonth + 1;
            const year = currYear;
            selectedDate = `${year}-${month < 10 ? '0' + month : month}-${dayNumber < 10 ? '0' + dayNumber : dayNumber}`;
        });
    });
}

renderCalendar();

prevNextIcon.forEach(icon => { // getting prev and next icons
    icon.addEventListener("click", () => { // adding click event on both icons
        // if clicked icon is previous icon then decrement current month by 1 else increment it by 1
        currMonth = icon.id === "prev" ? currMonth - 1 : currMonth + 1;

        if(currMonth < 0 || currMonth > 11) { // if current month is less than 0 or greater than 11
            // creating a new date of current year & month and pass it as date value
            date = new Date(currYear, currMonth, new Date().getDate());
            currYear = date.getFullYear(); // updating current year with new date year
            currMonth = date.getMonth(); // updating current month with new date month
        } else {
            date = new Date(); // pass the current date as date value
        }
        renderCalendar(); // calling renderCalendar function
    });
});

/* Style for the dot */
const style = document.createElement('style');
style.innerHTML = `
    .dot {
        width: 5px;
        height: 5px;
        background-color: #E8C340; /* Change color as needed */
        border-radius: 50%;
        position: absolute; /* Adjust position */
        top: 4px; /* Adjust as necessary */
        left: 50%; /* Center it */
        transform: translateX(-50%); /* Center it */
    }
    .active .dot {
        background-color: white;
    }
`;
document.head.appendChild(style);