<?php
session_start();
$pageTitle = 'Contact Messages';
include './init.php';

if (isset($_SESSION['username'])) {
    $do = isset($_GET['do']) ? $_GET['do'] : 'dashboard';
    
    // Fetch contact messages from the database
    $ListContacts = $con->prepare("SELECT * FROM `contacts` ORDER BY `created_c` DESC");
    $ListContacts->execute();
    $Contacts = $ListContacts->fetchAll(PDO::FETCH_ASSOC);
    
    if ($do == 'dashboard') {
?>
        <div class="contacts">
            <div class="container">
                <h1>Contact Messages</h1>
                <div class="table-responsive">
                    <?php if (isset($_SESSION['message'])) : ?>
                        <div id="message">
                            <?php echo $_SESSION['message']; ?>
                        </div>
                    <?php unset($_SESSION['message']); endif; ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr class="text-bg-light">
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($Contacts as $contact) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                    <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                    <td>
                                        <a href="javascript:void(0);" class="showSubject" data-subject="<?php echo htmlspecialchars($contact['subject']); ?>">
                                            <?php echo htmlspecialchars($contact['subject']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" class="showMessage" data-message="<?php echo htmlspecialchars($contact['message']); ?>">
                                            <?php echo substr(htmlspecialchars($contact['message']), 0, 50); ?>...
                                        </a>
                                    </td>
                                    <td><?php echo $contact['created_c']; ?></td>
                                    <td>
                                        
                                        <button type="button" class="btn btn-primary reply-btn" data-email="<?php echo $contact['email']; ?>" data-name="<?php echo $contact['name']; ?>" data-id="<?php echo $contact['id']; ?>">
                                            <i class="fa-solid fa-reply"></i>&nbsp;Reply
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <div id="replyPopup" class="popup" style="display:none;">
            <div class="popup-content">
                <span class="close" onclick="closeReplyPopup()">&times;</span>
                <h3>Reply to: <span id="recipient-name"></span></h3>
                <form action="send_reply.php" method="POST">
                    <input type="hidden" name="contact_id" id="contact_id">
                    <input type="hidden" name="recipient_email" id="recipient_email">
                    <textarea name="reply_message" id="reply_message" rows="6" placeholder="Type your reply here..." required></textarea>
                    <button type="submit" class="btn btn-success mt-3">Send Reply</button>
                </form>
            </div>
        </div>

        
        <div id="subjectPopup" class="popup" style="display:none;">
            <div class="popup-content">
                <span class="close" onclick="closeSubjectPopup()">&times;</span>
                <h3>Subject</h3>
                <p id="popup-subject"></p>
            </div>
        </div>

        
        <div id="messagePopup" class="popup" style="display:none;">
            <div class="popup-content">
                <span class="close" onclick="closeMessagePopup()">&times;</span>
                <h3>Message</h3>
                <p id="popup-message"></p>
            </div>
        </div>
<?php
    } elseif ($do == 'action') {
        if (isset($_POST['btn_delete'])) {
            $id = $_POST['id'];
            $stmt = $con->prepare("DELETE FROM contacts WHERE `contacts`.`id` = ?");
            $stmt->execute([$id]);
            $_SESSION['message'] = 'Contact message deleted successfully';
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    } else {
        header('location: admin_contacts.php');
        exit();
    }
} else {
    header('location: index.php');
    exit();
}

include $tpl . 'footer.php';
?>

<script>
document.querySelectorAll('.reply-btn').forEach(button => {
    button.addEventListener('click', function() {
        const email = this.getAttribute('data-email');
        const name = this.getAttribute('data-name');
        const id = this.getAttribute('data-id');
        
        document.getElementById('recipient-name').innerText = name;
        document.getElementById('recipient_email').value = email;
        document.getElementById('contact_id').value = id;
        
       
        document.getElementById('replyPopup').style.display = 'block';
        showOverlay(); 
    });
});

function closeReplyPopup() {
    document.getElementById('replyPopup').style.display = 'none';
    closeOverlay(); 
}


document.querySelectorAll('.showSubject').forEach(link => {
    link.addEventListener('click', function() {
        const subject = this.getAttribute('data-subject');
        document.getElementById('popup-subject').innerText = subject;
        document.getElementById('subjectPopup').style.display = 'block';
        showOverlay(); 
    });
});

function closeSubjectPopup() {
    document.getElementById('subjectPopup').style.display = 'none';
    closeOverlay(); 
}


document.querySelectorAll('.showMessage').forEach(link => {
    link.addEventListener('click', function() {
        const message = this.getAttribute('data-message');
        document.getElementById('popup-message').innerText = message;
        document.getElementById('messagePopup').style.display = 'block';
        showOverlay(); 
    });
});


function closeMessagePopup() {
    document.getElementById('messagePopup').style.display = 'none';
    closeOverlay(); 
}

function showOverlay() {
    const overlay = document.createElement('div');
    overlay.classList.add('popup-overlay');
    document.body.appendChild(overlay);
    overlay.addEventListener('click', function() {
        closeReplyPopup();
        closeSubjectPopup();
        closeMessagePopup();
        document.body.removeChild(overlay); 
    });
}

function closeOverlay() {
    const overlay = document.querySelector('.popup-overlay');
    if (overlay) {
        overlay.remove(); 
    }
}
</script>

<style>
.popup {
    display: none;
    position: absolute;
    top: 50%;  
    left: 50%; 
    transform: translate(-50%, -50%); 
    width: 60%; 
    max-width: 600px; 
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3); 
    z-index: 1000;  
}


.close {
    font-size: 30px;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
    position: absolute;
    top: 10px;
    right: 10px;
}

.close:hover {
    color: black;
}


.popup-content {
    text-align: center;
}


.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
}

#reply_message {
    width: 100%; 
    height: 150px; 
    border-radius: 8px;
    padding: 10px;
    resize: vertical; 
    border: 1px solid #ddd; 
    margin-bottom: 15px;
}

button[type="submit"] {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    background-color: #28a745;
    border: none;
    border-radius: 5px;
    color: white;
    cursor: pointer;
}

button[type="submit"]:hover {
    background-color: #218838;
}
</style>
