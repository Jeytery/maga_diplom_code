//
//  SignView.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 20.11.2024.
//

import SwiftUI

struct SignView: View {
    @State private var email: String = ""
    @State private var password: String = ""
    @State private var repeatPassword: String = ""

    var body: some View {
        NavigationView {
            List {
                Section {
                    TextField("Email", text: $email)
                        .textInputAutocapitalization(.none)
                        .autocorrectionDisabled(true)
                        .keyboardType(.emailAddress)
                }
                Section {
                    SecureField("Password", text: $password)
                    SecureField("Repeat Password", text: $repeatPassword)
                }
                Section {
                    HStack {
                        Spacer()
                        Text("Auth")
                            .foregroundColor(.white)
                            .font(.system(size: 16, weight: .semibold))
                        Spacer()
                    }
                    .listRowBackground(Color.blue)
                }
            }
            .listStyle(InsetGroupedListStyle())
            .navigationTitle("Auth")
        }
    }
}

#Preview {
    SignView()
}
