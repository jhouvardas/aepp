/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function change(x) {
  var txt;
  var person = prompt("Please enter your name:");
  if (person == null || person == "") {
    txt = "User cancelled the prompt.";
  } else {
    txt = "Hello " + person + "! How are you today?";
  }
  document.getElementById(x).innerHTML = txt;
}

function megaliterosApo(){
    var x,max,maxmax;
    max = -1;
    maxmax = -1;
    x = prompt("Δώστε αριθμό η -1 για τέλος");
    while(x != -1){
        if(x > maxmax){
            max = maxmax;
            maxmam = x;
        }else if(x > max){
            max = x;
        }
        x = prompt("Δώστε αριθμό η -1 για τέλος");
    }
    
}



function test(){
    var math = document.getElementById("math").value;
    var aoth = document.getElementById("aoth").value;
    var glossa = document.getElementById("glossa").value;
    var aepp = document.getElementById("aepp").value;
    var total1 = (parseFloat(math) + parseFloat(aoth) + parseFloat(glossa) + parseFloat(aepp))*2;
    var total2 = parseFloat(math)*1.3 + parseFloat(aoth)*0.7;
    var final = (total1 + total2)*100;
    document.getElementById("moria2").value = final;
}
