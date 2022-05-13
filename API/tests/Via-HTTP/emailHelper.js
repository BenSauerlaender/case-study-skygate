const Imap = require("imap");
const { simpleParser } = require("mailparser");
const imapConfig = {
  user: "trash-receiver@emailn.de",
  password: "gfcX5?nQ3Q#rrnHj",
  host: "imap.emailn.de",
  port: 993,
  tls: true,
};

exports.getEmail = async () => {
  return await new Promise((resolve, reject) => {
    try {
      const imap = new Imap(imapConfig);
      imap.once("ready", () => {
        imap.openBox("INBOX", false, () => {
          imap.search(["UNSEEN", ["SINCE", new Date()]], (err, results) => {
            if (err) {
              throw err;
            } else if (results && results.length > 0) {
              const f = imap.fetch(results, { bodies: "" });
              f.on("message", (msg) => {
                msg.on("body", (stream) => {
                  simpleParser(stream, async (err, parsed) => {
                    resolve(parsed);
                  });
                });
                msg.once("attributes", (attrs) => {
                  const { uid } = attrs;
                  imap.addFlags(uid, ["\\Seen"], () => {
                    // Mark the email as read after reading it
                    //console.log("Marked as read!");
                  });
                });
              });
              f.once("error", (ex) => {
                return Promise.reject(ex);
              });
              f.once("end", () => {
                //console.log("Done fetching all messages!");
                imap.end();
              });
            } else {
              resolve(false);
              imap.end();
            }
          });
        });
      });

      imap.once("error", (err) => {
        console.log(err);
      });

      imap.once("end", () => {
        console.log("Connection ended");
      });

      imap.connect();
    } catch (ex) {
      console.log("an error occurred");
    }
  });
};
