<?php

class FormMaker {

   
    public function addAskisiForm() {
        ?>
        <h1>Εισαγωγή Άσκησης Κοψίνης</h1>
        <div class="container">  
            <form action="<?php htmlspecialchars($_SERVER[PHP_SELF]) ?> " method="post">  
                <?php
                $this->selectStudent();
                $this->selectDate();
                $this->selectAskiseisSource();
                $this->selectAskiseisLocation();
                $this->getAskiseis();
                ?>                                                      
                <button type="submit" class="btn btn-primary" name="askiseis">Υποβολή</button>         
            </form>                
        </div>  
        <?php
    }

    public function addPanelliniesForm() {
        ?>
        <h1>Εισαγωγή Άσκησης Πανελληνίων</h1>
        <div class="container">  
            <form action="<?php htmlspecialchars($_SERVER[PHP_SELF]) ?> " method="post">  
                <?php
                $this->selectStudent();
                $this->selectPanelliniesYear();
                $this->selectThema();
                ?>
                <div class="form-group">
                    <label for="lykeio">Λύκειο</label>
                    <select class="form-control" id="sel1" name="lykeio">
                        <option value="Ημερήσιο">Ημερήσιο</option>
                        <option value="Εσπερινό">Εσπερινό</option>         
                    </select>
                </div>
                <div class="form-group">
                    <label for="period">Περίοδος</label>
                    <select class="form-control" id="sel1" name="period">
                        <option value="Απολυτήριες">Απολυτήριες</option>
                        <option value="Επαναληπτικές">Επαναληπτικές</option>         
                    </select>
                </div>
                <div class="form-group">
                    <label for="erotima">Ερώτημα</label>
                    <input type="text" class="form-control" name ="erotima">
                </div>
                <?php
                $this->selectDate();
                $this->selectAskiseisLocation();
                ?>                                                      
                <button type="submit" class="btn btn-primary" name="panellinies">Υποβολή</button>         
            </form>                
        </div>  
        <?php
    }

   

    public function addNoteForm() {
        ?>
        <h1>Νέα Σημείωση</h1>
        <div class="container">
            <form action="<?php htmlspecialchars($_SERVER[PHP_SELF]) ?> " method="post" >
                <?php $this->selectStudent(); ?>
                <?php $this->selectDate(); ?>
                <div class="form-group">
                    <label for="note">Σημείωση</label>
                    <textarea class="form-control" rows="4" name="note" id="note"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" name="submitNote">Υποβολή</button>
            </form>
        </div>
        <?php
    }
    
    public function addQuizForm() {
        ?>
        <h1>Νέο Quiz</h1>
        <div class="container">
            <form action="<?php htmlspecialchars($_SERVER[PHP_SELF]) ?> " method="post" >                
                <?php $this->selectDate(); ?>
                <div class="form-group">
                    <label for="note">Σημείωση</label>
                    <textarea class="form-control" rows="4" name="note" id="note"></textarea>
                </div>
                <div class="form-group">
                    <label for="note">Σημείωση</label>
                    <input type="<link rel="stylesheet" href="url"/>" class="form-control" rows="4" name="note" id="note"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" name="submitNote">Υποβολή</button>
            </form>
        </div>
        <?php
    }

   
    public function deleteLessonForm() {
        ?>
        <h1>Διαγραφή μαθήματος</h1>
        <div class="container">  
            <form action="<?php htmlspecialchars($_SERVER[PHP_SELF]) ?> "  method="post" >  
                <?php $this->selectStudentOnChangeSubmit(); ?>
                <?php //  $this->selectDate(); ?>
                <?php $this->selectLesson(); ?>

                <button type="submit" class="btn btn-primary">Υποβολή</button>         
            </form>                
        </div>  
        <?php
    }

   
   
    public function getStudentLessonsForm() {
        ?>
        <h1>Αναζήτηση μαθημάτων</h1>
        <div class="container">  
            <form action="<?php htmlspecialchars($_SERVER[PHP_SELF]) ?> " method="post">  
                <?php $this->selectStudent(); ?>
                <?php $this->selectDate(); ?>    
                <?php $this->selectToDateNotRequired(); ?> 
                <div class="form-group">
                    <div class="form-check">
                        <label class="form-check-label" for="lastName   ">
                            <input type="checkbox" class="form-check-input" id="lastName" name="lastName" value="yes">Επώνυμο
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <label class="form-check-label" for="radio1">
                            <input type="checkbox" class="form-check-input" id="showLocation" name="location" value="yes">Τοποθεσία
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" name="getStudentLessons">Υποβολή</button>         
            </form>                
        </div>  
        <?php
    }

    public function getStudentNotesForm() {
        ?>
        <h1>Αναζήτηση Σημειώσεων</h1>
        <div class="container">  
            <form action="<?php htmlspecialchars($_SERVER[PHP_SELF]) ?> " method="post">  
                <?php $this->selectStudent(); ?>
                <?php $this->selectDateNotRequired(); ?>    
                <?php // $this->selectToDateNotRequired(); ?> 
                <button type="submit" class="btn btn-primary" name="getStudentNotes">Υποβολή</button>         
            </form>                
        </div>  
        <?php
    }

   
   
    public function getStudentAskiseisForm() {
        ?>
        <h1>Αναζήτηση Ασκήσεων</h1>
        <div class="container">  
            <form action="<?php htmlspecialchars($_SERVER[PHP_SELF]) ?> " method="post">  
                <?php
                $this->selectStudent();
                $this->selectDate();
                $this->selectAskiseisLocation();
                ?>                                              
                <button type="submit" class="btn btn-primary" name="getStudentsAskiseis">Υποβολή</button>         
            </form>                
        </div>  
        <?php
    }

    public function getStudentPanelliniesForm() {
        ?>
        <h1>Αναζήτηση Ασκήσεων</h1>
        <div class="container">  
            <form action="<?php htmlspecialchars($_SERVER[PHP_SELF]) ?> " method="post">  
                <?php
                $this->selectStudent();
                $this->selectDate();
                $this->selectAskiseisLocation();
                ?>                                              
                <button type="submit" class="btn btn-primary" name="getStudentsPanellinies">Υποβολή</button>         
            </form>                
        </div>  
        <?php
    }

    public function selectDate() {
      ?>
        <div class="form-group">  
            <label for="date">Ημερομηνία:</label>             
            <input type="date" class="form-control" id="date" placeholder="Ημερομηνία" name="date" required>  
        </div> 
        <?php
    }

    public function selectToDateNotRequired() {
        ?>
        <div class="form-group">  
            <label for="date">Μέχρι Ημερομηνία:</label>             
            <input type="date" class="form-control" id="date" placeholder="Ημερομηνία" name="toDate">  
        </div> 
        <?php
    }

    public function selectDateNotRequired() {
        ?>
        <div class="form-group">  
            <label for="date">Ημερομηνία:</label>             
            <input type="date" class="form-control" id="date" placeholder="Ημερομηνία" name="date">  
        </div> 
        <?php
    }

    public function selectLesson() {
        $lessonList = new DbHandler;
        ?>
        <div class="form-group">         
            <label for="lesson">Μάθημα:</label>  
            <select class="form-control" id="lessonId" name="lessonId" >             
                <?php
                $result = $lessonList->getLessons();
                echo '<option value=""></option>';
                while ($row = $result->fetch_assoc()) {
                    $date = date_create($row['date']);
                    echo'<option value="' . $row['lessonId'] . '">' . date_format($date, "D d/m/y") . '</option>';
                }
                ?>
            </select>             
        </div>
        <?php
    }

   
   
   
    public function selectPanelliniesYear() {
        ?>
        <div class="form-group">
            <lable for="panelliniesYear">Έτος:</lable>
            <select class="form-control" id="panelliniesYear" name="panelliniesYear">
                <?php
                $year = 2001;
                echo '<option></option>';
                while ($year <= 2021) {
                    echo "<option value=$year>$year</option>";
                    $year++;
                }
                ?>
            </select>
        </div>
        <?php
    }

    public function selectThema() {
        ?>
        <div class="form-group">
            <lable for="thema">Θέμα:</lable>
            <select class="form-control" id="thema" name="thema">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select>
        </div>
        <?php
    }

    public function selectAskiseisSource() {
        ?>
        <div class="form-group">
            <lable for="source">Πηγή:</lable>
            <select class="form-control" id="source" name="source" required> 
                <option></option>
                <option value="Κοψίνης 1">Κοψίνης 1</option>
                <option value="Κοψίνης 2">Κοψίνης 2</option>
                <option value="Κοψίνης 3">Κοψίνης 3</option>
                <option value="Πανελλήνιες">Πανελλήνιες</option>
            </select>
        </div>
        <?php
    }

   
    public function getAskiseis() {
        ?>
        <div class="form-group">
            <label for="askiseis">Ασκήσεις</label>
            <div class="table-responsive-sm">
                <table>
                    <tr>
                        <td>1</td><td><input type="number" name="1" step=".01"></td>                        
                    </tr>
                    <tr>
                        <td>2</td><td><input type="number" name="2" step=".01"></td>                        
                    </tr>
                    <tr>
                        <td>3</td><td><input type="number" name="3" step=".01"></td>                        
                    </tr>
                    <tr>
                        <td>4</td><td><input type="number" name="4" step=".01"></td>                        
                    </tr>
                    <tr>
                        <td>5</td><td><input type="number" name="5" step=".01"></td>                        
                    </tr>
                    <tr>
                        <td>6</td><td><input type="number" name="6" step=".01"></td>                        
                    </tr>
                    <tr>
                        <td>7</td><td><input type="number" name="7" step=".01"></td>                        
                    </tr>
                    <tr>
                        <td>8</td><td><input type="number" name="8" step=".01"></td>                        
                    </tr>
                    <tr>
                        
                        <td>9</td><td><input type="number" name="9" step=".01"></td>                        
                    </tr>
                    <tr>
                        <td>10</td><td><input type="number" name="10" step=".01"></td>                        
                    </tr>
                </table>
            </div>
        </div>
        </div>
        <?php
    }

    public function selectStudentOnChangeSubmit() {
        $studentList = new DbHandler();
        ?>
        <div class="form-group">         
            <label for="student">Μαθητής:</label>  
            <select class="form-control" id="studentId" name="studentId" required onchange="this.form.submit()">             
                <?php
                $result = $studentList->getStudents();
                echo '<option value=""></option>';
                echo '<option value="6974004099">Όλοι</option>';
                while ($row = $result->fetch_assoc()) {
                    echo'<option value="' . $row['studentId'] . '">' . $row['name'] . ' ' . $row['lastName'] . '</option>';
                }
                ?>
                <script type="text/javascript">
                    document.getElementById('studentId').value = "<?php echo $_POST['studentId']; ?>";
                </script>
            </select>             
        </div>
        <?php
    }

   
    public function loginForm() {
        ?>    
        <div class="container">
            <form action="authenticate.php" class="needs-validation" novalidate method="post">
                <div class="form-group">
                    <label for="uname">Username:</label>
                    <input type="text" class="form-control" id="uname" placeholder="Enter username" name="username" required>
                    <div class="valid-feedback">Valid.</div>
                    <div class="invalid-feedback">Please fill out this field.</div>
                </div>
                <div class="form-group">
                    <label for="pwd">Password:</label>
                    <input type="password" class="form-control" id="pwd" placeholder="Enter password" name="password" required>
                    <div class="valid-feedback">Valid.</div>
                    <div class="invalid-feedback">Please fill out this field.</div>
                </div>            
                <button type="submit" class="btn btn-primary" name="login">Submit</button>
            </form>
        </div>        
        <?php
        $this->addFormValidation();
    }

    public function addFormValidation() {
        ?>
        <script>
            // Disable form submissions if there are invalid fields
            (function () {
                'use strict';
                window.addEventListener('load', function () {
                    // Get the forms we want to add validation styles to
                    var forms = document.getElementsByClassName('needs-validation');
                    // Loop over them and prevent submission
                    var validation = Array.prototype.filter.call(forms, function (form) {
                        form.addEventListener('submit', function (event) {
                            if (form.checkValidity() === false) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                            form.classList.add('was-validated');
                        }, false);
                    });
                }, false);
            })();
        </script> 
        <?php
    }

}
