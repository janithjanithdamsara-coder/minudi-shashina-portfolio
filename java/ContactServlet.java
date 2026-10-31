
package com.minudi.portfolio;

import java.io.IOException;
import java.io.PrintWriter;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

@WebServlet("/contact")
public class ContactServlet extends HttpServlet {

    @Override
    protected void doPost(HttpServletRequest request,
            HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");

        String name = request.getParameter("name");
        String email = request.getParameter("email");
        String message = request.getParameter("message");

        response.setContentType("text/html;charset=UTF-8");

        PrintWriter out = response.getWriter();

        out.println("<html><body style='background:#0b0b12;"
                + "color:white;text-align:center;padding:50px;"
                + "font-family:Arial'>");

        out.println("<h1 style='color:#a970ff'>Thank You!</h1>");

        out.println("<h2>Message Received Successfully</h2>");

        out.println("<p>Name: " + escape(name) + "</p>");
        out.println("<p>Email: " + escape(email) + "</p>");
        out.println("<p>Message: " + escape(message) + "</p>");

        out.println("<a href='index.html' style='color:#a970ff'>"
                + "Back to Home</a>");

        out.println("</body></html>");
    }

    private String escape(String value) {
        if (value == null) return "";

        return value.replace("&", "&amp;")
                    .replace("<", "&lt;")
                    .replace(">", "&gt;")
                    .replace("\"", "&quot;")
                    .replace("'", "&#39;");
    }
}
